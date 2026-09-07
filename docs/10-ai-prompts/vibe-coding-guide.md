# Vibe Coding Guide — How to use docs with AI without sharing full code

## Goal
Enable AI to implement a feature with only minimal context (global + module slice + relevant workflow/code snippet).

## Method
1. **Prepare packet**:
   ```
   global-context.md
   + 03-modules/{target}/logic.md
   + 03-modules/{target}/api-contract.md
   + 03-modules/{target}/dependencies.md
   + 04-workflows/{relevant}.md (if flow task)
   + 06-integrations/{relevant}.md (if sms/shipping/storage)
   + Short code slice (only the file/method to change, not whole repo)
   ```
2. **Prompt template**:
   ```
   You are working on Shopora (Laravel 11, MySQL, Blade).
   Context:
   [paste global-context]
   [paste module logic + api-contract]
   [paste workflow if needed]
   Rules:
   - Source of Truth is actual code, not Laravel best practice
   - Keep Controller->Service->Model pattern
   - Use DTO if module has DTO, else direct Request validated
   - Respect critical gotchas: toman->rial '0', linkable_id string, session keys, is_admin bypass
   - Do not invent Repository/Event
   - Do not fix unrelated bugs
   Task: [describe feature]
   Relevant code:
   [paste slice]
   ```
3. **Verify AI output**:
   - Check against `gotchas.md` (unit, string id, double restore, cache flush)
   - Run `php artisan test` if tests exist
   - Check `request-logging` sensitive filter

## Example packets
- Add new payment gateway: global + payments logic/api/gotchas + payment-zarinpal workflow + Payments folder code
- Add product filter: global + products logic + Filterable trait + search module
- Add shipping method: global + shipping logic + chapar doc (BROKEN note)

## Don'ts
- Don't share entire repo — window limit.
- Don't share .env secrets.
- Don't ask AI to refactor architecture — document only current behavior.

## Docs maintenance
After feature merged, update corresponding module docs (logic/api/dependencies/gotchas) and 11-metrics.
