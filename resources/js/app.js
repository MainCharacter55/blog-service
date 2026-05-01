import './bootstrap';
import { initPostFeedInteractions } from './modules/post-feed';
import { initPostShowInteractions } from './modules/post-show';
import { initGuestAuthWarning } from './modules/guest-auth-warning';

document.addEventListener('DOMContentLoaded', () => {
	initPostFeedInteractions(document);
	initPostShowInteractions(document);
	initGuestAuthWarning(document);
});
