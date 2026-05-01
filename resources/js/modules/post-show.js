export function initPostShowInteractions(root = document) {
    const menuWrappers = Array.from(root.querySelectorAll('[data-comment-menu-wrapper]'));
    const reactionMenuWrappers = Array.from(root.querySelectorAll('[data-reaction-menu-wrapper]'));
    const toggleReplyButtons = Array.from(root.querySelectorAll('[data-toggle-replies]'));
    const allReplyFormContainers = Array.from(root.querySelectorAll('[data-reply-form-container]'));

    if (
        menuWrappers.length === 0 &&
        reactionMenuWrappers.length === 0 &&
        toggleReplyButtons.length === 0
    ) {
        return;
    }

    const closeAllMenus = () => {
        Array.from(document.querySelectorAll('[data-comment-menu-wrapper]')).forEach((wrapper) => {
            const button = wrapper.querySelector('[data-comment-menu-button]');
            const menu = wrapper.querySelector('[data-comment-menu]');

            if (menu) {
                menu.classList.add('hidden');
            }

            if (button) {
                button.setAttribute('aria-expanded', 'false');
            }
        });

        Array.from(document.querySelectorAll('[data-reaction-menu-wrapper]')).forEach((wrapper) => {
            const button = wrapper.querySelector('[data-reaction-menu-button]');
            const menu = wrapper.querySelector('[data-reaction-menu]');

            if (menu) {
                menu.classList.add('hidden');
            }

            if (button) {
                button.setAttribute('aria-expanded', 'false');
            }
        });
    };

    toggleReplyButtons.forEach((button) => {
        if (button.dataset.postShowBound === '1') {
            return;
        }
        button.dataset.postShowBound = '1';

        button.addEventListener('click', (event) => {
            event.preventDefault();

            const commentId = button.dataset.commentId;
            const repliesContainer = root.querySelector(`[data-replies-container="${commentId}"]`);
            const replyFormContainer = root.querySelector(`[data-reply-form-container="${commentId}"]`);
            const replyTextarea = root.querySelector(`[data-reply-textarea="${commentId}"]`);

            const isOpen =
                (repliesContainer && !repliesContainer.classList.contains('hidden')) ||
                (replyFormContainer && !replyFormContainer.classList.contains('hidden'));

            const shouldOpen = !isOpen;

            // Toggle only the target comment's replies and reply form.
            // Close any other open reply *forms* so only one reply input is visible at a time,
            // but keep replies lists open to avoid collapsing nested threads.
            allReplyFormContainers.forEach((container) => {
                if (container !== replyFormContainer) {
                    container.classList.add('hidden');
                }
            });

            if (repliesContainer) {
                repliesContainer.classList.toggle('hidden', !shouldOpen);
            }

            if (replyFormContainer) {
                replyFormContainer.classList.toggle('hidden', !shouldOpen);
            }

            if (shouldOpen && replyTextarea) {
                replyTextarea.focus();
            }
        });
    });

    menuWrappers.forEach((wrapper) => {
        const button = wrapper.querySelector('[data-comment-menu-button]');
        const menu = wrapper.querySelector('[data-comment-menu]');

        if (!button || !menu || button.dataset.postShowBound === '1') {
            return;
        }

        button.dataset.postShowBound = '1';

        button.addEventListener('click', (event) => {
            event.stopPropagation();

            const isOpen = !menu.classList.contains('hidden');

            closeAllMenus();

            if (!isOpen) {
                menu.classList.remove('hidden');
                button.setAttribute('aria-expanded', 'true');
            }
        });

        menu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    reactionMenuWrappers.forEach((wrapper) => {
        const button = wrapper.querySelector('[data-reaction-menu-button]');
        const menu = wrapper.querySelector('[data-reaction-menu]');

        if (!button || !menu || button.dataset.postShowBound === '1') {
            return;
        }

        button.dataset.postShowBound = '1';

        button.addEventListener('click', (event) => {
            event.stopPropagation();

            const isOpen = !menu.classList.contains('hidden');

            closeAllMenus();

            if (!isOpen) {
                menu.classList.remove('hidden');
                button.setAttribute('aria-expanded', 'true');
            }
        });

        menu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    if (document.body.dataset.postShowDocumentBound !== '1') {
        document.body.dataset.postShowDocumentBound = '1';

        document.addEventListener('click', () => {
            closeAllMenus();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAllMenus();
            }
        });
    }

    async function replaceSectionsFromHtml(htmlText) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlText, 'text/html');
        const newComments = doc.querySelector('#comments');
        const newPostReactions = doc.querySelector('[data-post-reactions]');
        const currentComments = document.querySelector('#comments');
        const currentPostReactions = document.querySelector('[data-post-reactions]');

        // Save which reply threads are open before replacing DOM
        const openReplyContainers = new Set();
        Array.from(document.querySelectorAll('[data-replies-container]:not(.hidden)')).forEach((container) => {
            const commentId = container.getAttribute('data-replies-container');
            if (commentId) {
                openReplyContainers.add(commentId);
            }
        });

        // Save which reply forms are currently open before replacing DOM
        const openReplyForms = new Set();
        Array.from(document.querySelectorAll('[data-reply-form-container]:not(.hidden)')).forEach((container) => {
            const commentId = container.getAttribute('data-reply-form-container');
            if (commentId) {
                openReplyForms.add(commentId);
            }
        });

        if (newComments && currentComments) {
            currentComments.replaceWith(newComments);
        }

        if (newPostReactions && currentPostReactions) {
            currentPostReactions.replaceWith(newPostReactions);
        }

        // Restore open reply threads after DOM replacement
        openReplyContainers.forEach((commentId) => {
            const repliesContainer = document.querySelector(`[data-replies-container="${commentId}"]`);
            if (repliesContainer) {
                repliesContainer.classList.remove('hidden');
            }
        });

        // Restore open reply forms after DOM replacement
        openReplyForms.forEach((commentId) => {
            const replyFormContainer = document.querySelector(`[data-reply-form-container="${commentId}"]`);
            if (replyFormContainer) {
                replyFormContainer.classList.remove('hidden');
            }
        });

        // Re-init interactions on the newly injected content
        initPostShowInteractions(document);
    }

    function getSectionRefreshUrl() {
        const url = new URL(window.location.href);
        const refreshUrl = new URL(url.origin + url.pathname);
        const commentSort = url.searchParams.get('comment_sort');

        if (commentSort) {
            refreshUrl.searchParams.set('comment_sort', commentSort);
        }

        return refreshUrl.toString();
    }

    if (document.body.dataset.postShowAjaxSubmitBound !== '1') {
        document.body.dataset.postShowAjaxSubmitBound = '1';

        document.addEventListener('submit', async (event) => {
            const target = event.target;
            if (!(target instanceof HTMLFormElement)) {
                return;
            }

            const form = target.closest('form[data-ajax]');
            if (!form) {
                return;
            }

            event.preventDefault();

            const action = form.getAttribute('action') || window.location.href;
            const method = (form.getAttribute('method') || 'POST').toUpperCase();
            const formData = new FormData(form);
            const ajaxType = form.dataset.ajax;

            try {
                const res = await fetch(action, {
                    method,
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'text/html, application/json',
                    },
                });

                const contentType = res.headers.get('content-type') || '';

                if (contentType.includes('application/json')) {
                    const data = await res.json();
                    if (data.success) {
                        if (ajaxType === 'comment') {
                            const textarea = document.querySelector('#content');
                            if (textarea) {
                                textarea.value = '';
                            }
                        }

                        const refreshUrl = getSectionRefreshUrl();
                        const pageRes = await fetch(refreshUrl, { cache: 'no-store' });
                        const pageHtml = await pageRes.text();
                        await replaceSectionsFromHtml(pageHtml);
                        window.history.replaceState({}, '', refreshUrl);
                    } else {
                        alert(data.message || '操作に失敗しました');
                    }

                    return;
                }

                const text = await res.text();

                if (ajaxType === 'comment') {
                    const textarea = document.querySelector('#content');
                    if (textarea) {
                        textarea.value = '';
                    }
                }

                if (res.redirected) {
                    const refreshUrl = getSectionRefreshUrl();
                    const pageRes = await fetch(refreshUrl, { cache: 'no-store' });
                    const pageHtml = await pageRes.text();
                    await replaceSectionsFromHtml(pageHtml);
                    window.history.replaceState({}, '', refreshUrl);
                } else {
                    await replaceSectionsFromHtml(text);
                }
            } catch (err) {
                console.error(err);
                window.location.reload();
            }
        });
    }
}
