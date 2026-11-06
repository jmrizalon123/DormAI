(function () {
    'use strict';

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function linkifyAssistantHtml(text) {
        var escaped = escapeHtml(text);
        return escaped.replace(/https?:\/\/[^\s<]+/g, function (url) {
            return (
                '<a href="' +
                url +
                '" target="_blank" rel="noopener noreferrer" class="da-listing-assistant__link">' +
                url +
                '</a>'
            );
        });
    }

    function appendBubble(messagesEl, role, text) {
        if (!messagesEl) return;
        var wrap = document.createElement('div');
        wrap.className = 'da-listing-assistant__msg da-listing-assistant__msg--' + role;
        var inner = document.createElement('div');
        inner.className = 'da-listing-assistant__bubble';
        if (role === 'assistant') {
            inner.innerHTML = linkifyAssistantHtml(text).replace(/\n/g, '<br>');
        } else {
            inner.innerHTML = escapeHtml(text).replace(/\n/g, '<br>');
        }
        wrap.appendChild(inner);
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function btnClassForVariant(variant) {
        if (variant === 'primary') return 'btn btn-primary btn-sm da-listing-assistant__card-btn';
        if (variant === 'accent') return 'btn btn-sm da-listing-assistant__card-btn da-listing-assistant__card-btn--accent';
        return 'btn btn-outline-secondary btn-sm da-listing-assistant__card-btn';
    }

    function renderListingCard(card) {
        var article = document.createElement('article');
        article.className = 'da-listing-assistant__card';
        if (!card || card.kind !== 'listing') {
            return article;
        }

        var header = document.createElement('header');
        header.className = 'da-listing-assistant__card-head';
        var head = document.createElement('div');
        head.className = 'da-listing-assistant__card-title';
        head.textContent = card.title || 'Listing';
        header.appendChild(head);
        if (card.location) {
            var loc = document.createElement('div');
            loc.className = 'da-listing-assistant__card-location';
            loc.textContent = card.location;
            header.appendChild(loc);
        }
        article.appendChild(header);

        var meta = document.createElement('div');
        meta.className = 'da-listing-assistant__card-meta';
        meta.setAttribute('role', 'group');
        meta.setAttribute('aria-label', 'Listing details');
        var rate = document.createElement('span');
        rate.className = 'da-listing-assistant__card-pill';
        rate.textContent = card.rate_label || '—';
        meta.appendChild(rate);
        if (card.beds_available != null) {
            var beds = document.createElement('span');
            beds.className = 'da-listing-assistant__card-pill da-listing-assistant__card-pill--muted';
            beds.textContent = card.beds_available + ' bed(s) free';
            meta.appendChild(beds);
        }
        if (card.wifi) {
            var wifi = document.createElement('span');
            wifi.className = 'da-listing-assistant__card-pill da-listing-assistant__card-pill--wifi';
            wifi.textContent = 'Wi‑Fi incl.';
            meta.appendChild(wifi);
        }
        article.appendChild(meta);

        if (card.tagline) {
            var tag = document.createElement('p');
            tag.className = 'da-listing-assistant__card-tagline';
            tag.textContent = card.tagline;
            article.appendChild(tag);
        }

        var owner = card.owner || {};
        var contact = document.createElement('section');
        contact.className = 'da-listing-assistant__card-contact';
        contact.setAttribute('aria-label', 'Operator contact');
        var contactTitle = document.createElement('div');
        contactTitle.className = 'da-listing-assistant__card-contact-label';
        contactTitle.textContent = 'Operator';
        contact.appendChild(contactTitle);
        if (owner.name) {
            var nm = document.createElement('div');
            nm.className = 'da-listing-assistant__card-contact-name';
            nm.textContent = owner.name;
            contact.appendChild(nm);
        }
        if (owner.phone) {
            var phoneRow = document.createElement('div');
            phoneRow.className = 'da-listing-assistant__card-contact-row';
            var tel = String(owner.phone).replace(/\s+/g, '');
            phoneRow.innerHTML =
                '<span class="da-listing-assistant__card-contact-dt">Phone</span>' +
                '<span class="da-listing-assistant__card-contact-dd">' +
                '<a href="tel:' +
                escapeHtml(tel) +
                '" class="da-listing-assistant__card-tel">' +
                escapeHtml(String(owner.phone)) +
                '</a></span>';
            contact.appendChild(phoneRow);
        }
        if (owner.email) {
            var em = document.createElement('div');
            em.className = 'da-listing-assistant__card-contact-row';
            em.innerHTML =
                '<span class="da-listing-assistant__card-contact-dt">Email</span>' +
                '<span class="da-listing-assistant__card-contact-dd">' +
                '<a href="mailto:' +
                escapeHtml(String(owner.email)) +
                '" class="da-listing-assistant__card-mail">' +
                escapeHtml(String(owner.email)) +
                '</a></span>';
            contact.appendChild(em);
        }
        if (!owner.name && !owner.phone && !owner.email) {
            var empty = document.createElement('p');
            empty.className = 'da-listing-assistant__card-contact-empty';
            empty.textContent = 'No phone or email on file — use Message operator below.';
            contact.appendChild(empty);
        }
        article.appendChild(contact);

        var ctaPrimary = card.cta_primary;
        var operatorQuick = Array.isArray(card.operator_quick) ? card.operator_quick : [];
        var contactForm = card.contact_form;
        var structured = !!(ctaPrimary && ctaPrimary.href) || operatorQuick.length > 0 || !!(contactForm && contactForm.href);

        if (structured) {
            var footer = document.createElement('footer');
            footer.className = 'da-listing-assistant__card-footer';

            if (ctaPrimary && ctaPrimary.href) {
                var rowP = document.createElement('div');
                rowP.className = 'da-listing-assistant__card-row da-listing-assistant__card-row--primary';
                var p = document.createElement('a');
                p.href = ctaPrimary.href;
                p.className = btnClassForVariant(ctaPrimary.variant || 'primary');
                p.textContent = ctaPrimary.label || 'View listing';
                if (/^https?:\/\//.test(ctaPrimary.href)) {
                    p.target = '_blank';
                    p.rel = 'noopener noreferrer';
                }
                rowP.appendChild(p);
                footer.appendChild(rowP);
            }

            if (operatorQuick.length) {
                var qLabel = document.createElement('div');
                qLabel.className = 'da-listing-assistant__card-footer-label';
                qLabel.textContent = 'Quick contact';
                footer.appendChild(qLabel);
                var quickRow = document.createElement('div');
                quickRow.className = 'da-listing-assistant__card-quick';
                operatorQuick.forEach(function (q) {
                    if (!q || !q.href) return;
                    var b = document.createElement('a');
                    b.href = q.href;
                    b.className = 'btn btn-outline-secondary btn-sm da-listing-assistant__card-quick-btn';
                    b.textContent = q.label || 'Open';
                    quickRow.appendChild(b);
                });
                footer.appendChild(quickRow);
            }

            if (contactForm && contactForm.href) {
                var mLabel = document.createElement('div');
                mLabel.className = 'da-listing-assistant__card-footer-label';
                mLabel.textContent = 'Written message';
                footer.appendChild(mLabel);
                var rowM = document.createElement('div');
                rowM.className = 'da-listing-assistant__card-row da-listing-assistant__card-row--message';
                var m = document.createElement('a');
                m.href = contactForm.href;
                m.className = btnClassForVariant(contactForm.variant || 'accent');
                m.textContent = contactForm.label || 'Message operator';
                if (/^https?:\/\//.test(contactForm.href)) {
                    m.target = '_blank';
                    m.rel = 'noopener noreferrer';
                }
                rowM.appendChild(m);
                footer.appendChild(rowM);
                if (contactForm.hint) {
                    var hint = document.createElement('p');
                    hint.className = 'da-listing-assistant__card-form-hint';
                    hint.textContent = contactForm.hint;
                    footer.appendChild(hint);
                }
            }

            article.appendChild(footer);
            return article;
        }

        var actions = Array.isArray(card.actions) ? card.actions : [];
        if (actions.length) {
            var actWrap = document.createElement('div');
            actWrap.className = 'da-listing-assistant__card-actions';
            actions.forEach(function (a) {
                if (!a || !a.href) return;
                var link = document.createElement('a');
                link.href = a.href;
                link.className = btnClassForVariant(a.variant);
                link.textContent = a.label || 'Open';
                if (/^https?:\/\//.test(a.href)) {
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                }
                var row = document.createElement('div');
                row.className = 'da-listing-assistant__card-action-row';
                row.appendChild(link);
                if (a.note) {
                    var note = document.createElement('div');
                    note.className = 'da-listing-assistant__card-action-note';
                    note.textContent = a.note;
                    row.appendChild(note);
                }
                actWrap.appendChild(row);
            });
            article.appendChild(actWrap);
        }

        return article;
    }

    function appendAssistantMessage(messagesEl, text, cards) {
        if (!messagesEl) return;
        var wrap = document.createElement('div');
        wrap.className = 'da-listing-assistant__msg da-listing-assistant__msg--assistant';
        var inner = document.createElement('div');
        inner.className = 'da-listing-assistant__bubble';
        inner.innerHTML = linkifyAssistantHtml(text || '').replace(/\n/g, '<br>');
        wrap.appendChild(inner);
        var list = Array.isArray(cards) ? cards : [];
        if (list.length) {
            var deck = document.createElement('div');
            deck.className = 'da-listing-assistant__cards';
            list.forEach(function (c) {
                deck.appendChild(renderListingCard(c));
            });
            wrap.appendChild(deck);
        }
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function initRoot(root) {
        var endpoint = root.getAttribute('data-endpoint');
        if (!endpoint) return;

        var intro =
            root.getAttribute('data-intro') ||
            'Hi — ask about rate, location, amenities, policies, or how to reserve a bed for this listing.';
        var placeholderFallback = root.getAttribute('data-placeholder') || '';
        var quickRaw = root.getAttribute('data-quick-replies') || '';
        var quickList = quickRaw
            ? quickRaw.split('|').map(function (s) {
                  return s.trim();
              }).filter(Boolean)
            : [];

        var toggle = root.querySelector('[data-da-assistant-toggle]');
        var panel = root.querySelector('[data-da-assistant-panel]');
        var closeBtn = root.querySelector('[data-da-assistant-close]');
        var form = root.querySelector('[data-da-assistant-form]');
        var input = root.querySelector('[data-da-assistant-input]');
        var messagesEl = root.querySelector('[data-da-assistant-messages]');
        var sendBtn = root.querySelector('[data-da-assistant-send]');
        var sendLabel = sendBtn && sendBtn.querySelector('.da-listing-assistant__send-label');
        var sendSpinner = sendBtn && sendBtn.querySelector('.da-listing-assistant__send-loading');
        var quickWrap = root.querySelector('[data-da-assistant-quick-wrap]');
        var quickRow = root.querySelector('[data-da-assistant-quick]');
        var welcomeRaw = root.getAttribute('data-welcome-payload');
        var welcomePayload = null;
        if (welcomeRaw) {
            try {
                welcomePayload = JSON.parse(welcomeRaw);
            } catch (ignore) {
                welcomePayload = null;
            }
        }
        var autoWelcomeOnce = root.getAttribute('data-auto-open-welcome-once') === 'true';

        if (placeholderFallback && input && !input.getAttribute('placeholder')) {
            input.setAttribute('placeholder', placeholderFallback);
        }

        if (quickList.length && quickRow && quickWrap) {
            quickWrap.classList.remove('d-none');
            quickList.forEach(function (label) {
                var chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'da-listing-assistant__chip';
                chip.textContent = label;
                chip.addEventListener('click', function () {
                    if (input) input.value = label;
                    if (form) form.requestSubmit();
                });
                quickRow.appendChild(chip);
            });
        }

        var history = [];
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '';

        function setOpen(open) {
            if (!panel || !toggle) return;
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open && input) {
                setTimeout(function () {
                    input.focus();
                }, 50);
            }
        }

        if (messagesEl && messagesEl.children.length === 0) {
            if (
                welcomePayload &&
                typeof welcomePayload.reply === 'string' &&
                Array.isArray(welcomePayload.cards)
            ) {
                appendAssistantMessage(messagesEl, welcomePayload.reply, welcomePayload.cards);
                history.push({ role: 'assistant', content: welcomePayload.reply });
                if (autoWelcomeOnce) {
                    try {
                        var k = 'dormai.marketplace.welcome.opened.v1';
                        if (!localStorage.getItem(k)) {
                            localStorage.setItem(k, '1');
                            setOpen(true);
                        }
                    } catch (ignoreOpen) {}
                }
            } else {
                appendBubble(messagesEl, 'assistant', intro);
            }
        }

        function setLoading(loading) {
            if (!sendBtn || !input) return;
            sendBtn.disabled = loading;
            input.disabled = loading;
            if (sendLabel) sendLabel.classList.toggle('d-none', loading);
            if (sendSpinner) sendSpinner.classList.toggle('d-none', !loading);
        }

        function sendMessage(text) {
            if (!text || !endpoint || !form) return;

            appendBubble(messagesEl, 'user', text);
            history.push({ role: 'user', content: text });

            setLoading(true);

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    message: text,
                    messages: history.slice(0, -1),
                }),
            })
                .then(function (res) {
                    return res.json().then(function (data) {
                        if (!res.ok) {
                            throw new Error((data && data.error) || 'Request failed');
                        }
                        return data;
                    });
                })
                .then(function (data) {
                    var reply =
                        typeof data.reply === 'string'
                            ? data.reply
                            : data.reply && typeof data.reply.reply === 'string'
                              ? data.reply.reply
                              : '';
                    var cards = Array.isArray(data.cards)
                        ? data.cards
                        : data.reply && Array.isArray(data.reply.cards)
                          ? data.reply.cards
                          : [];
                    appendAssistantMessage(messagesEl, reply, cards);
                    history.push({ role: 'assistant', content: reply });
                })
                .catch(function (err) {
                    appendBubble(
                        messagesEl,
                        'assistant',
                        err && err.message
                            ? String(err.message)
                            : 'Something went wrong. Please try again.'
                    );
                })
                .finally(function () {
                    setLoading(false);
                });
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                setOpen(panel && panel.hidden);
            });
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                setOpen(false);
            });
        }

        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (form) form.requestSubmit();
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var text = input && input.value ? input.value.trim() : '';
                if (!text || !endpoint) return;
                if (input) input.value = '';
                sendMessage(text);
            });
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('[data-da-assistant] [data-da-assistant-panel]').forEach(function (panel) {
            if (!panel.hidden) {
                panel.hidden = true;
                var rootPanel = panel.closest('[data-da-assistant]');
                var toggle = rootPanel && rootPanel.querySelector('[data-da-assistant-toggle]');
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });

    document.querySelectorAll('[data-da-assistant]').forEach(function (root) {
        initRoot(root);
    });
})();
