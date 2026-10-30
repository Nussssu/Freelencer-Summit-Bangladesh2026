{{--
    reCAPTCHA without touching the Vue forms: every same-origin POST (Inertia sends forms through XMLHttpRequest) waits
    for a fresh single-use token and carries it in the X-Recaptcha-Token header, which VerifyRecaptcha checks.
--}}
@if ($recaptcha = \App\Support\Settings::recaptcha())
    @php($v3 = ($recaptcha['version'] ?? 'v3') === 'v3')
    <script src="https://www.google.com/recaptcha/api.js?{{ $v3 ? 'render='.urlencode($recaptcha['site_key']) : 'render=explicit' }}" async defer></script>
    <script>
    (function () {
        var key = @json($recaptcha['site_key']), v3 = @json($v3), widget = null, pending = [];
        function token() {
            return new Promise(function (resolve) {
                var go = function () {
                    if (v3) return grecaptcha.execute(key, { action: 'submit' }).then(resolve, function () { resolve(''); });
                    if (widget === null) {
                        var el = document.createElement('div');
                        document.body.appendChild(el);
                        widget = grecaptcha.render(el, { sitekey: key, size: 'invisible', badge: 'bottomleft',
                            callback: function (t) { pending.splice(0).forEach(function (r) { r(t); }); grecaptcha.reset(widget); } });
                    }
                    pending.push(resolve);
                    grecaptcha.execute(widget);
                };
                window.grecaptcha && grecaptcha.ready ? grecaptcha.ready(go) : setTimeout(function () { window.grecaptcha ? grecaptcha.ready(go) : resolve(''); }, 1500);
            });
        }
        var open = XMLHttpRequest.prototype.open, send = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.open = function (method, url) {
            this.__rc = String(method).toUpperCase() === 'POST' && new URL(url, location.href).origin === location.origin;
            return open.apply(this, arguments);
        };
        XMLHttpRequest.prototype.send = function (body) {
            if (!this.__rc) return send.call(this, body);
            var xhr = this;
            token().then(function (t) { if (t) xhr.setRequestHeader('X-Recaptcha-Token', t); send.call(xhr, body); });
        };
    })();
    </script>
@endif
