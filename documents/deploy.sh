#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════
# Joobjoob — Automated Deployment Script (Steps 4–9)
# Run via SSH after uploading code to the server.
#
# Usage:
#   chmod +x deploy.sh && ./deploy.sh
#
# What it does:
#   Step 4: composer install --no-dev
#   Step 5: npm ci && npm run build
#   Step 6: Set permissions (storage, bootstrap/cache, public/uploads)
#   Step 7: php artisan migrate --force + essential seeders
#   Step 8: php artisan storage:link
#   Step 9: Clear and rebuild config/route/view caches
# ══════════════════════════════════════════════════════════════
set -euo pipefail

# ── Configuration ────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
APP_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
LOG_FILE="${APP_DIR}/storage/logs/deploy-$(date +%Y%m%d-%H%M%S).log"
ERRORS=0

# ── Helpers ──────────────────────────────────────────────────
info()  { echo -e "\033[1;34m[INFO]\033[0m  $*"; }
ok()    { echo -e "\033[1;32m[OK]\033[0m    $*"; }
warn()  { echo -e "\033[1;33m[WARN]\033[0m  $*"; }
fail()  { echo -e "\033[1;31m[FAIL]\033[0m  $*"; ((ERRORS++)); }

log() {
    local msg="[$(date '+%Y-%m-%d %H:%M:%S')] $*"
    echo "$msg" >> "$LOG_FILE"
    echo "$msg"
}

# ── Preflight Checks ────────────────────────────────────────
cd "$APP_DIR" || { echo "Cannot cd to $APP_DIR"; exit 1; }
mkdir -p storage/logs
log "Deployment started in $APP_DIR"

# Check .env exists
if [ ! -f .env ]; then
    fail ".env file not found! Copy .env.example → .env and configure it first."
    exit 1
fi

# Check DB_CONNECTION is NOT sqlite (P0-07)
if grep -qE '^DB_CONNECTION=sqlite' .env; then
    fail "DB_CONNECTION=sqlite detected in .env — production MUST use mysql!"
    exit 1
fi

# Check APP_KEY is set
if grep -qE '^APP_KEY=$' .env; then
    warn "APP_KEY is empty — will be set after composer install"
fi

# ══════════════════════════════════════════════════════════════
# Step 4 — Composer Install (Production)
# ══════════════════════════════════════════════════════════════
log "Step 4: composer install --no-dev --optimize-autoloader"
if COMPOSER_MEMORY_LIMIT=-1 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress 2>> "$LOG_FILE"; then
    ok "Step 4: Composer install completed"
else
    fail "Step 4: Composer install FAILED — check $LOG_FILE"
fi

# ══════════════════════════════════════════════════════════════
# Step 5 — Node Build (Vite)
# ══════════════════════════════════════════════════════════════
if command -v npm &>/dev/null; then
    log "Step 5: npm ci && npm run build"
    if npm ci --no-audit --no-fund 2>> "$LOG_FILE" && \
       npm run build 2>> "$LOG_FILE"; then
        ok "Step 5: Vite build completed"
    else
        fail "Step 5: npm build FAILED — check $LOG_FILE"
    fi

    # Verify manifest exists
    if [ -f public/build/manifest.json ]; then
        ok "Step 5: public/build/manifest.json exists"
    else
        fail "Step 5: public/build/manifest.json MISSING — CSS/JS will 404!"
    fi
else
    warn "Step 5: npm not found — skipping (ensure public/build/ is uploaded manually)"
fi

# ══════════════════════════════════════════════════════════════
# Step 6 — Permissions
# ══════════════════════════════════════════════════════════════
log "Step 6: Setting permissions"
chmod -R 775 storage bootstrap/cache 2>> "$LOG_FILE" || warn "chmod storage failed (may need root)"
chmod -R 775 public/uploads 2>> "$LOG_FILE" || warn "chmod public/uploads failed"
# Try chown (may fail on shared hosting — that's OK)
chown -R "$(whoami):www-data" storage bootstrap/cache 2>/dev/null || true
ok "Step 6: Permissions set"

# ══════════════════════════════════════════════════════════════
# Step 7 — Migrate + Seed
# ══════════════════════════════════════════════════════════════
log "Step 7: php artisan migrate --force"
if php artisan migrate --force 2>> "$LOG_FILE"; then
    ok "Step 7: Migrations completed"
else
    fail "Step 7: Migration FAILED"
fi

# Seed only essential tables (NOT full DatabaseSeeder — avoids test data)
log "Step 7: Seeding essential data"
php artisan db:seed --class=AdminOwnerSeeder --force 2>> "$LOG_FILE" \
    && ok "Step 7a: AdminOwnerSeeder seeded" \
    || warn "Step 7a: AdminOwnerSeeder skipped (already exists or error)"

php artisan db:seed --class=OrderShippingStatusSeeder --force 2>> "$LOG_FILE" \
    && ok "Step 7b: OrderShippingStatusSeeder seeded" \
    || warn "Step 7b: OrderShippingStatusSeeder skipped (already exists or error)"

# ══════════════════════════════════════════════════════════════
# Step 8 — Storage Link
# ══════════════════════════════════════════════════════════════
log "Step 8: Storage symlink"
if [ ! -L public/storage ]; then
    php artisan storage:link 2>> "$LOG_FILE" || {
        warn "storage:link failed — trying manual symlink"
        ln -s ../storage/app/public public/storage 2>> "$LOG_FILE" || \
            warn "Manual symlink also failed (symlinks may be disabled on host)"
    }
fi
ok "Step 8: Storage link checked"

# ══════════════════════════════════════════════════════════════
# Step 9 — Cache
# ══════════════════════════════════════════════════════════════
log "Step 9: Clearing old caches"
php artisan config:clear 2>> "$LOG_FILE"
php artisan route:clear 2>> "$LOG_FILE"
php artisan view:clear 2>> "$LOG_FILE"
php artisan cache:clear 2>> "$LOG_FILE"

log "Step 9: Building fresh caches"
if php artisan config:cache 2>> "$LOG_FILE"; then
    ok "Step 9a: config:cache OK"
else
    fail "Step 9a: config:cache FAILED"
fi

if php artisan route:cache 2>> "$LOG_FILE"; then
    ok "Step 9b: route:cache OK"
else
    fail "Step 9b: route:cache FAILED"
fi

if php artisan view:cache 2>> "$LOG_FILE"; then
    ok "Step 9c: view:cache OK"
else
    fail "Step 9c: view:cache FAILED"
fi

# ══════════════════════════════════════════════════════════════
# Summary
# ══════════════════════════════════════════════════════════════
echo ""
log "═════════════════════════════════════════════"
if [ "$ERRORS" -eq 0 ]; then
    ok "Deployment completed successfully!"
    log "Next manual steps:"
    log "  1. curl -I https://yourdomain.ir/up       (should return 200)"
    log "  2. curl -I https://yourdomain.ir/          (should return 200)"
    log "  3. php artisan about                       (verify drivers)"
    log "  4. Set up cron job (Step 10):"
    log "     * * * * * cd /path/to/joobjoob && php artisan schedule:run >> /dev/null 2>&1"
    log "  5. Run smoke tests (Step 14)"
    log "  6. Tag v1.0.0: git tag v1.0.0 && git push --tags"
else
    fail "Deployment finished with $ERRORS error(s). Check $LOG_FILE"
    exit 1
fi
