<script>
    window.addEventListener('DOMContentLoaded', function () {
        function getLength() {
            const titleSeoElements = document.querySelectorAll('.seoTitle{{$data['id']}}');
            const smallTitle = document.getElementById('smallTitle{{$data['id']}}');
            const desSeoElements = document.querySelectorAll('.seoDes{{$data['id']}}');
            const smallDes = document.getElementById('smallDes{{$data['id']}}');
            const titleH1Elements = document.querySelectorAll('.seoH1{{$data['id']}}');
            const smallH1 = document.getElementById('smallH1{{$data['id']}}');
            titleSeoElements.forEach((titleSeo) => {
                if (titleSeo.value.length !== 0) {
                    smallTitle.textContent = `کاراکتر  های نوشته شده: ${titleSeo.value.length}`;
                } else {
                    smallTitle.textContent = '';
                }
                titleSeo.addEventListener('input', function () {
                    if (titleSeo.value.length !== 0) {
                        smallTitle.textContent = `کاراکتر  های نوشته شده: ${titleSeo.value.length}`;
                    } else {
                        smallTitle.textContent = '';
                    }
                });
            });
            desSeoElements.forEach((desSeo) => {
                if (desSeo.value.length !== 0) {
                    smallDes.textContent = `کاراکتر  های نوشته شده: ${desSeo.value.length}`;
                } else {
                    smallDes.textContent = '';
                }
                desSeo.addEventListener('input', function () {
                    if (desSeo.value.length !== 0) {
                        smallDes.textContent = `کاراکتر  های نوشته شده: ${desSeo.value.length}`;
                    } else {
                        smallDes.textContent = '';
                    }
                });
            });
            titleH1Elements.forEach((H1) => {
                if (H1.value.length !== 0) {
                    smallH1.textContent = `کاراکتر  های نوشته شده: ${H1.value.length}`;
                } else {
                    smallH1.textContent = '';
                }
                H1.addEventListener('input', function () {
                    if (H1.value.length !== 0) {
                        smallH1.textContent = `کاراکتر  های نوشته شده: ${H1.value.length}`;
                    } else {
                        smallH1.textContent = '';
                    }
                });
            });
        }
        getLength();
    });
</script>
