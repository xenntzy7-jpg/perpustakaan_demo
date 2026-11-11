(function () {
    var status = document.querySelector(".navigation-progress__status");
    var root = document.documentElement;

    if (!status) {
        return;
    }

    function showLoading() {
        root.classList.add("is-page-loading");
        status.textContent = "Memuat halaman.";
    }

    function hideLoading() {
        root.classList.remove("is-page-loading");
        status.textContent = "";
    }

    if (document.readyState !== "complete") {
        showLoading();
        window.addEventListener("load", hideLoading, { once: true });
    }

    window.addEventListener("pageshow", hideLoading);

    document.addEventListener("click", function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        var target = event.target;
        var link = target instanceof Element ? target.closest("a[href]") : null;
        if (!link || link.hasAttribute("download") || (link.target && link.target !== "_self")) {
            return;
        }

        var destination = new URL(link.href, window.location.href);
        if (destination.origin !== window.location.origin || destination.href === window.location.href) {
            return;
        }

        var logoutPath = new URL("logout.php", window.location.href).pathname;
        if (destination.pathname === logoutPath && !window.confirm("Yakin ingin keluar dari akun?")) {
            event.preventDefault();
            return;
        }

        if (destination.pathname === window.location.pathname
            && destination.search === window.location.search
            && destination.hash !== ""
        ) {
            return;
        }

        showLoading();
    });

    document.addEventListener("submit", function (event) {
        if (event.defaultPrevented) {
            return;
        }

        var form = event.target;
        if (!(form instanceof HTMLFormElement) || (form.target && form.target !== "_self")) {
            return;
        }

        var destination = new URL(form.action, window.location.href);
        if (destination.origin === window.location.origin) {
            showLoading();
        }
    });
})();
