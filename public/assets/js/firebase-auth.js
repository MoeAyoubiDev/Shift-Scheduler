(function () {
    function getAppConfig() {
        return window.AppConfig || {};
    }

    function initFirebase() {
        if (!window.firebase || !window.firebase.auth) {
            return null;
        }

        var config = getAppConfig().firebase || {};
        if (!config.apiKey || !config.projectId) {
            return null;
        }

        if (firebase.apps && firebase.apps.length === 0) {
            firebase.initializeApp(config);
        }

        return firebase.auth();
    }

    function setError(message) {
        var errorEl = document.getElementById('firebase-auth-error');
        if (!errorEl) {
            return;
        }

        if (message) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        } else {
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        }
    }

    function getFriendlyFirebaseError(error) {
        var code = error && error.code ? String(error.code) : '';
        var message = error && error.message ? String(error.message) : '';
        if (code) {
            return message ? (code + ': ' + message) : code;
        }
        return message || 'Authentication error. Please try again.';
    }

    function setFieldError(fieldId, message) {
        var fieldError = document.querySelector('[data-error-for="' + fieldId + '"]');
        if (!fieldError) {
            return;
        }
        fieldError.textContent = message || '';
        fieldError.style.display = message ? 'block' : 'none';
    }

    function clearFieldErrors(form) {
        if (!form) {
            return;
        }
        var errors = form.querySelectorAll('[data-error-for]');
        errors.forEach(function (errorEl) {
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        });
    }

    function setLoading(button, isLoading) {
        if (!button) {
            return;
        }
        button.disabled = isLoading;
        if (isLoading) {
            button.classList.add('loading');
        } else {
            button.classList.remove('loading');
        }
    }

    function sendTokenToBackend(firebaseToken, payloadOverrides) {
        var payload = {
            action: 'firebase_login'
        };

        if (payloadOverrides) {
            Object.keys(payloadOverrides).forEach(function (key) {
                payload[key] = payloadOverrides[key];
            });
        }

        return fetch('/auth/firebase-login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Authorization': 'Bearer ' + firebaseToken
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json();
        });
    }

    function handleBackendResponse(response) {
        if (!response || response.success === false) {
            setError((response && response.message) ? response.message : 'Authentication failed.');
            return;
        }

        if (response.redirect) {
            window.location.href = response.redirect;
            return;
        }

        window.location.href = '/index.php';
    }

    function getSignupMetadata() {
        var resolved = Intl.DateTimeFormat().resolvedOptions();
        var timezone = resolved.timeZone || 'UTC';
        var locale = resolved.locale || navigator.language || '';
        var country = '';
        if (locale.indexOf('-') !== -1) {
            country = locale.split('-')[1];
        } else if (locale.indexOf('_') !== -1) {
            country = locale.split('_')[1];
        }

        return {
            timezone: timezone,
            country: country
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        var auth = initFirebase();
        if (!auth) {
            return;
        }

        var loginForm = document.getElementById('firebase-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function (event) {
                event.preventDefault();
                setError('');
                clearFieldErrors(loginForm);

                var emailInput = loginForm.querySelector('input[name="firebase_email"]');
                var passwordInput = loginForm.querySelector('input[name="firebase_password"]');
                var submitButton = loginForm.querySelector('.firebase-submit');

                var email = emailInput ? emailInput.value.trim() : '';
                var password = passwordInput ? passwordInput.value : '';

                var hasError = false;
                if (!email) {
                    setFieldError('firebase-email', 'Email is required.');
                    hasError = true;
                }
                if (!password) {
                    setFieldError('firebase-password', 'Password is required.');
                    hasError = true;
                }
                if (hasError) {
                    return;
                }

                setLoading(submitButton, true);

                auth.signInWithEmailAndPassword(email, password)
                    .then(function (result) {
                        if (!result || !result.user) {
                            throw new Error('Authentication failed.');
                        }
                        return result.user.getIdToken(true);
                    })
                    .then(function (idToken) {
                        return sendTokenToBackend(idToken, { action: 'firebase_login' });
                    })
                    .then(handleBackendResponse)
                    .catch(function (error) {
                        setError(getFriendlyFirebaseError(error));
                    })
                    .finally(function () {
                        setLoading(submitButton, false);
                    });
            });
        }

        var signupForm = document.getElementById('firebase-signup-form');
        if (signupForm) {
            signupForm.addEventListener('submit', function (event) {
                event.preventDefault();
                setError('');
                clearFieldErrors(signupForm);

                var companyInput = signupForm.querySelector('input[name="company_name"]');
                var emailInput = signupForm.querySelector('input[name="admin_email"]');
                var passwordInput = signupForm.querySelector('input[name="admin_password"]');
                var submitButton = signupForm.querySelector('.firebase-submit');

                var companyName = companyInput ? companyInput.value.trim() : '';
                var email = emailInput ? emailInput.value.trim() : '';
                var password = passwordInput ? passwordInput.value : '';

                var hasError = false;
                if (!companyName) {
                    setFieldError('signup-company-name', 'Company name is required.');
                    hasError = true;
                }
                if (!email) {
                    setFieldError('signup-admin-email', 'Admin email is required.');
                    hasError = true;
                }
                if (!password) {
                    setFieldError('signup-password', 'Password is required.');
                    hasError = true;
                } else if (password.length < 8) {
                    setFieldError('signup-password', 'Password must be at least 8 characters.');
                    hasError = true;
                }
                if (hasError) {
                    return;
                }

                var signupMeta = getSignupMetadata();

                setLoading(submitButton, true);

                auth.createUserWithEmailAndPassword(email, password)
                    .then(function (result) {
                        if (!result || !result.user) {
                            throw new Error('Authentication failed.');
                        }
                        return result.user.getIdToken(true);
                    })
                    .then(function (idToken) {
                        return sendTokenToBackend(idToken, {
                            action: 'firebase_signup',
                            company_name: companyName,
                            admin_email: email,
                            timezone: signupMeta.timezone,
                            country: signupMeta.country
                        });
                    })
                    .then(handleBackendResponse)
                    .catch(function (error) {
                        setError(getFriendlyFirebaseError(error));
                    })
                    .finally(function () {
                        setLoading(submitButton, false);
                    });
            });
        }
    });
})();
