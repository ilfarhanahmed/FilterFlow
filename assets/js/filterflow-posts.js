(function () {
	'use strict';

	class FilterFlowWidget {
		constructor(root) {
			if (!root) {
				return;
			}

			if (root.__filterFlowInstance) {
				root.__filterFlowInstance.refreshFromDom();
				return root.__filterFlowInstance;
			}

			root.dataset.filterflowReady = 'true';
			root.__filterFlowInstance = this;
			this.root = root;
			this.elementorWrapper = root.closest('.elementor-element');
			this.results = root.querySelector('.ffp-results');
			this.grid = root.querySelector('.ffp-grid');
			this.pagination = root.querySelector('.ffp-pagination');
			this.status = root.querySelector('.ffp-status');
			this.filterBar = root.querySelector('.ffp-filter-bar');
			this.filterInner = root.querySelector('.ffp-filter-bar__inner');
			this.filterChips = root.querySelector('.ffp-filter-chips');
			this.termButtons = Array.from(root.querySelectorAll('.ffp-filter-chips .ffp-chip--term'));
			this.allButton = root.querySelector('.ffp-filter-chips .ffp-chip--all');
			this.moreButton = root.querySelector('.ffp-more-trigger');
			this.overflowMenu = root.querySelector('.ffp-overflow-menu');
			this.tabletSelectTrigger = root.querySelector('.ffp-tablet-select-trigger');
			this.tabletActiveLabel = root.querySelector('.ffp-tablet-active-label');
			this.mobileTrigger = root.querySelector('.ffp-mobile-filter-trigger');
			this.mobileAllButton = root.querySelector('.ffp-mobile-all-trigger');
			this.mobileQuickContainer = root.querySelector('.ffp-mobile-quick-filters');
			this.mobileQuickButtons = Array.from(root.querySelectorAll('.ffp-mobile-quick-chip'));
			this.tabletLayout = this.normalizeTabletLayout(root.dataset.tabletFilterLayout || 'select');
			this.mobileLayout = this.normalizeMobileLayout(root.dataset.mobileFilterLayout || 'fixed-all');
			this.mobileActivePrefix = root.dataset.mobileActivePrefix || '';
			this.allLabel = root.dataset.allLabel || 'All';
			this.tabletBreakpoint = this.clampNumber(root.dataset.tabletBreakpoint, 700, 1600, 1024);
			this.mobileBreakpoint = this.clampNumber(root.dataset.mobileBreakpoint, 320, 1024, 767);
			this.mobileBreakpoint = Math.min(this.mobileBreakpoint, this.tabletBreakpoint - 1);
			this.headerCollisionGuard = root.dataset.headerCollisionGuard !== 'no';
			this.headerCollisionGap = this.clampNumber(root.dataset.headerCollisionGap, 0, 120, 16);
			this.sheet = root.querySelector('.ffp-sheet');
			this.backdrop = root.querySelector('.ffp-sheet-backdrop');
			this.activeTerm = 0;
			this.pendingTerm = 0;
			this.abortController = null;
			this.lastFocused = null;
			this.layoutFrame = 0;
			this.currentMode = '';
			this.resizeObserver = null;
			this.settingsObserver = null;

			try {
				this.settings = JSON.parse(root.dataset.settings || '{}');
			} catch (error) {
				this.settings = {};
			}

			this.portalFilterPanel();
			this.portalOverflowMenu();
			this.refreshFromDom(false);
			this.bindEvents();
			this.observeSize();
			this.observeSettings();
			this.updateResponsiveMode();
			this.syncSelection();
			this.scheduleLayout();
			window.setTimeout(() => this.scheduleLayout(), 180);
		}

		clampNumber(value, minimum, maximum, fallback) {
			const parsed = Number(value);
			if (!Number.isFinite(parsed)) {
				return fallback;
			}
			return Math.min(maximum, Math.max(minimum, parsed));
		}

		normalizeTabletLayout(layout) {
			return layout === 'auto-fit' ? 'auto-fit' : 'select';
		}

		normalizeMobileLayout(layout) {
			if (layout === 'filter-all-only' || layout === 'button-only') {
				return 'filter-all-only';
			}
			if (layout === 'quick-scroll' || layout === 'swipe-only') {
				return layout;
			}
			return 'fixed-all';
		}



		getPrefixedLayout(prefix, allowed) {
			const elements = [this.root, this.elementorWrapper].filter(Boolean);
			for (const element of elements) {
				for (const className of Array.from(element.classList || [])) {
					if (!className.startsWith(prefix)) {
						continue;
					}
					const value = className.slice(prefix.length);
					if (allowed.includes(value)) {
						return value;
					}
				}
			}
			return '';
		}

		refreshFromDom(schedule = true) {
			const mobileFromClass = this.getPrefixedLayout(
				'ffp-mobile-layout-',
				['fixed-all', 'filter-all-only', 'quick-scroll', 'swipe-only']
			);
			const tabletFromClass = this.getPrefixedLayout(
				'ffp-tablet-layout-',
				['select', 'auto-fit']
			);

			const nextMobile = this.normalizeMobileLayout(mobileFromClass || this.root.dataset.mobileFilterLayout || 'fixed-all');
			const nextTablet = this.normalizeTabletLayout(tabletFromClass || this.root.dataset.tabletFilterLayout || 'select');
			const changed = nextMobile !== this.mobileLayout || nextTablet !== this.tabletLayout;

			this.mobileLayout = nextMobile;
			this.tabletLayout = nextTablet;
			if (this.root.dataset.mobileFilterLayout !== nextMobile) {
				this.root.dataset.mobileFilterLayout = nextMobile;
			}
			if (this.root.dataset.tabletFilterLayout !== nextTablet) {
				this.root.dataset.tabletFilterLayout = nextTablet;
			}

			this.syncPortalTheme();
			if (schedule || changed) {
				this.scheduleLayout();
			}
		}

		observeSettings() {
			if (typeof MutationObserver !== 'function') {
				return;
			}
			this.settingsObserver = new MutationObserver(() => this.refreshFromDom());
			this.settingsObserver.observe(this.root, {
				attributes: true,
				attributeFilter: ['class', 'data-mobile-filter-layout', 'data-tablet-filter-layout']
			});
			if (this.elementorWrapper && this.elementorWrapper !== this.root) {
				this.settingsObserver.observe(this.elementorWrapper, {
					attributes: true,
					attributeFilter: ['class']
				});
			}
		}

		portalFilterPanel() {
			if (!this.sheet || !this.backdrop || this.sheet.dataset.filterflowPortal === 'true') {
				return;
			}

			const owner = this.root.id || `ffp-owner-${Math.random().toString(36).slice(2)}`;
			document.querySelectorAll('.ffp-sheet--portal, .ffp-sheet-backdrop--portal').forEach((portal) => {
				if (portal.dataset.ffpOwner === owner && portal !== this.sheet && portal !== this.backdrop) {
					portal.remove();
				}
			});
			document.body.classList.remove('ffp-sheet-open');

			this.sheet.dataset.filterflowPortal = 'true';
			this.backdrop.dataset.filterflowPortal = 'true';
			this.sheet.dataset.ffpOwner = owner;
			this.backdrop.dataset.ffpOwner = owner;
			this.sheet.classList.add('ffp-sheet--portal');
			this.backdrop.classList.add('ffp-sheet-backdrop--portal');
			document.body.appendChild(this.backdrop);
			document.body.appendChild(this.sheet);
			this.syncPortalTheme();
		}

		portalOverflowMenu() {
			if (!this.overflowMenu || this.overflowMenu.dataset.filterflowPortal === 'true') {
				return;
			}

			const owner = this.root.id || `ffp-owner-${Math.random().toString(36).slice(2)}`;
			document.querySelectorAll('.ffp-overflow-menu--portal').forEach((portal) => {
				if (portal.dataset.ffpOwner === owner && portal !== this.overflowMenu) {
					portal.remove();
				}
			});

			this.overflowMenu.dataset.filterflowPortal = 'true';
			this.overflowMenu.dataset.ffpOwner = owner;
			this.overflowMenu.classList.add('ffp-overflow-menu--portal');
			document.body.appendChild(this.overflowMenu);
		}

		positionOverflowMenu() {
			if (!this.moreButton || !this.overflowMenu || this.overflowMenu.hidden) {
				return;
			}

			const rect = this.moreButton.getBoundingClientRect();
			const viewportWidth = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
			const viewportHeight = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
			const margin = 12;
			const width = Math.min(300, Math.max(210, rect.width));

			this.overflowMenu.style.width = `${width}px`;
			this.overflowMenu.style.maxHeight = `${Math.max(160, viewportHeight - (margin * 2))}px`;

			const measuredHeight = Math.min(340, this.overflowMenu.scrollHeight || 340);
			let left = rect.right - width;
			left = Math.max(margin, Math.min(left, viewportWidth - width - margin));

			let top = rect.bottom + 8;
			if (top + measuredHeight > viewportHeight - margin && rect.top - measuredHeight - 8 >= margin) {
				top = rect.top - measuredHeight - 8;
			}

			this.overflowMenu.style.left = `${Math.round(left)}px`;
			this.overflowMenu.style.top = `${Math.round(Math.max(margin, top))}px`;
		}

		syncPortalTheme() {
			if (!this.sheet || !this.root) {
				return;
			}
			const computed = window.getComputedStyle(this.root);
			const variables = {
				'--ffp-filter-icon-size': '18px',
				'--ffp-filter-icon-gap': '12px',
				'--ffp-mobile-filter-bg': '#07131c',
				'--ffp-mobile-filter-text': '#ffffff',
				'--ffp-filter-active-bg': '#2563eb',
				'--ffp-filter-active-text': '#ffffff'
			};
			Object.entries(variables).forEach(([name, fallback]) => {
				const value = computed.getPropertyValue(name).trim() || fallback;
				this.sheet.style.setProperty(name, value);
				if (this.overflowMenu) {
					this.overflowMenu.style.setProperty(name, value);
				}
			});
			this.sheet.style.fontFamily = computed.fontFamily;
			if (this.overflowMenu) {
				this.overflowMenu.style.fontFamily = computed.fontFamily;
			}
		}

		bindEvents() {
			// Keep normal delegated handlers for pagination and the filter sheet.
			// Filter chips and the More control also use a window-level capture
			// router below so they remain interactive when themes/page builders
			// stop click propagation in compact desktop layouts.
			this.root.addEventListener('click', (event) => this.handleClick(event));
			this.root.addEventListener('keydown', (event) => this.handleKeydown(event));
			if (this.sheet) { this.sheet.addEventListener('click', (event) => this.handleClick(event)); this.sheet.addEventListener('keydown', (event) => this.handleKeydown(event)); }
			if (this.backdrop) { this.backdrop.addEventListener('click', (event) => this.handleClick(event)); }
			if (this.overflowMenu) { this.overflowMenu.addEventListener('click', (event) => this.handleClick(event)); this.overflowMenu.addEventListener('keydown', (event) => this.handleKeydown(event)); }

			this.pointerCandidate = null;
			this.lastPointerActivation = null;
			this.boundWindowControlClick = (event) => this.handleWindowControlClick(event);
			this.boundWindowPointerDown = (event) => this.handleWindowPointerDown(event);
			this.boundWindowPointerUp = (event) => this.handleWindowPointerUp(event);
			this.boundWindowPointerCancel = (event) => this.handleWindowPointerCancel(event);
			window.addEventListener('click', this.boundWindowControlClick, true);
			window.addEventListener('pointerdown', this.boundWindowPointerDown, true);
			window.addEventListener('pointerup', this.boundWindowPointerUp, true);
			window.addEventListener('pointercancel', this.boundWindowPointerCancel, true);

			document.addEventListener('click', (event) => {
				const insideWidget = this.root.contains(event.target);
				const insideOverflow = this.overflowMenu && this.overflowMenu.contains(event.target);
				if (!insideWidget && !insideOverflow) {
					this.closeOverflow();
				}
			});

			window.addEventListener('resize', () => { this.scheduleLayout(); this.positionOverflowMenu(); }, { passive: true });
			// Do not recalculate layout-affecting header clearance on every scroll.
			// Repeated padding changes can make mobile browsers jump vertically.
			window.addEventListener('scroll', () => { this.scheduleStickyHeaderOffset(); this.positionOverflowMenu(); }, { passive: true });
			window.addEventListener('load', () => this.scheduleLayout(), { once: true });
			if (document.fonts && document.fonts.ready) {
				document.fonts.ready.then(() => this.scheduleLayout()).catch(() => {});
			}
		}

		getWindowControl(event) {
			const target = event && event.target instanceof Element ? event.target : null;
			if (!target) {
				return null;
			}

			return target.closest('.ffp-more-trigger, .ffp-overflow-item[data-term], .ffp-chip[data-term]');
		}

		ownsWindowControl(control) {
			if (!control) {
				return false;
			}

			return this.root.contains(control)
				|| Boolean(this.overflowMenu && this.overflowMenu.contains(control));
		}

		getControlKey(control) {
			if (!control) {
				return '';
			}
			if (control.matches('.ffp-more-trigger')) {
				return 'more';
			}
			return `${control.classList.contains('ffp-overflow-item') ? 'overflow' : 'chip'}:${control.dataset.term || '0'}`;
		}

		consumeControlEvent(event) {
			if (!event) {
				return;
			}
			event.__filterFlowHandled = true;
			if (event.cancelable) {
				event.preventDefault();
			}
			if (typeof event.stopImmediatePropagation === 'function') {
				event.stopImmediatePropagation();
			} else if (typeof event.stopPropagation === 'function') {
				event.stopPropagation();
			}
		}

		activateWindowControl(control, event, source = 'click') {
			if (!this.ownsWindowControl(control)) {
				return false;
			}

			this.consumeControlEvent(event);
			const key = this.getControlKey(control);

			if (source === 'pointer') {
				this.lastPointerActivation = { key, at: performance.now() };
			}

			if (control.matches('.ffp-more-trigger')) {
				this.toggleOverflow();
				return true;
			}

			if (control.matches('.ffp-overflow-item[data-term]')) {
				this.selectTerm(Number(control.dataset.term || 0), true);
				this.closeOverflow();
				return true;
			}

			if (control.matches('.ffp-chip[data-term]')) {
				this.selectTerm(Number(control.dataset.term || 0), true);
				return true;
			}

			return false;
		}

		handleWindowControlClick(event) {
			if (event.__filterFlowHandled) {
				return;
			}

			const control = this.getWindowControl(event);
			if (!this.ownsWindowControl(control)) {
				return;
			}

			const key = this.getControlKey(control);
			const recentPointer = this.lastPointerActivation
				&& this.lastPointerActivation.key === key
				&& performance.now() - this.lastPointerActivation.at < 900;

			if (recentPointer) {
				this.consumeControlEvent(event);
				return;
			}

			this.activateWindowControl(control, event, 'click');
		}

		handleWindowPointerDown(event) {
			if (!event.isPrimary) {
				return;
			}

			const control = this.getWindowControl(event);
			if (!this.ownsWindowControl(control)) {
				return;
			}

			this.pointerCandidate = {
				pointerId: event.pointerId,
				control,
				x: event.clientX,
				y: event.clientY,
				at: performance.now()
			};
		}

		handleWindowPointerUp(event) {
			const candidate = this.pointerCandidate;
			this.pointerCandidate = null;

			if (!candidate || candidate.pointerId !== event.pointerId || !this.ownsWindowControl(candidate.control)) {
				return;
			}

			const distance = Math.hypot(event.clientX - candidate.x, event.clientY - candidate.y);
			const elapsed = performance.now() - candidate.at;

			// Treat a short stationary pointer gesture as a tap/click. A genuine
			// horizontal swipe exceeds this threshold and remains scroll-only.
			if (distance <= 10 && elapsed <= 1000) {
				this.activateWindowControl(candidate.control, event, 'pointer');
			}
		}

		handleWindowPointerCancel(event) {
			if (this.pointerCandidate && this.pointerCandidate.pointerId === event.pointerId) {
				this.pointerCandidate = null;
			}
		}

		unbindWindowControlEvents() {
			if (this.boundWindowControlClick) {
				window.removeEventListener('click', this.boundWindowControlClick, true);
				window.removeEventListener('pointerdown', this.boundWindowPointerDown, true);
				window.removeEventListener('pointerup', this.boundWindowPointerUp, true);
				window.removeEventListener('pointercancel', this.boundWindowPointerCancel, true);
				this.boundWindowControlClick = null;
			}
		}

		observeSize() {
			if (typeof ResizeObserver !== 'function') {
				return;
			}

			this.resizeObserver = new ResizeObserver(() => this.scheduleLayout());
			this.resizeObserver.observe(this.root);
			if (this.filterInner) {
				this.resizeObserver.observe(this.filterInner);
			}

			if (this.headerCollisionGuard) {
				this.getHeaderCandidates().forEach((candidate) => {
					if (!this.root.contains(candidate) && !candidate.contains(this.root)) {
						this.resizeObserver.observe(candidate);
					}
				});
			}
		}

		scheduleLayout() {
			if (!this.root.isConnected) {
				this.unbindWindowControlEvents();
				if (this.resizeObserver) {
					this.resizeObserver.disconnect();
				}
				if (this.settingsObserver) {
					this.settingsObserver.disconnect();
				}
				if (this.sheet?.dataset.ffpOwner === this.root.id) {
					this.sheet.remove();
					this.backdrop?.remove();
				}
				if (this.overflowMenu?.dataset.ffpOwner === this.root.id) {
					this.overflowMenu.remove();
				}
				return;
			}

			window.cancelAnimationFrame(this.layoutFrame);
			this.layoutFrame = window.requestAnimationFrame(() => {
				this.arrangeFilters();
				this.applyHeaderCollisionGuard();
			});
		}

		updateResponsiveMode() {
			const width = Math.round(this.root.getBoundingClientRect().width || this.root.clientWidth || window.innerWidth);
			let mode = 'desktop';

			if (width <= this.mobileBreakpoint) {
				mode = 'mobile';
			} else if (width <= this.tabletBreakpoint) {
				mode = 'tablet';
			}

			this.root.classList.toggle('ffp-size-desktop', mode === 'desktop');
			this.root.classList.toggle('ffp-size-tablet', mode === 'tablet');
			this.root.classList.toggle('ffp-size-mobile', mode === 'mobile');
			this.root.dataset.filterflowSize = mode;
			this.root.dataset.tabletFilterLayout = this.tabletLayout;
			this.root.dataset.mobileFilterLayout = this.mobileLayout;
			if (this.sheet) { this.sheet.classList.toggle('ffp-sheet--mobile', mode === 'mobile'); this.sheet.classList.toggle('ffp-sheet--tablet', mode === 'tablet'); }

			if (mode === 'desktop' && this.currentMode && this.sheet && !this.sheet.hidden) {
				this.closeSheet();
			}

			this.currentMode = mode;
			return mode;
		}

		handleClick(event) {
			if (event.__filterFlowHandled) {
				return;
			}

			const chip = event.target.closest('.ffp-chip[data-term]');
			if (chip && this.root.contains(chip)) {
				event.preventDefault();
				this.selectTerm(Number(chip.dataset.term || 0), true);
				return;
			}

			const overflowItem = event.target.closest('.ffp-overflow-item[data-term]');
			if (overflowItem && this.overflowMenu && this.overflowMenu.contains(overflowItem)) {
				event.preventDefault();
				this.selectTerm(Number(overflowItem.dataset.term || 0), true);
				this.closeOverflow();
				return;
			}

			const moreTrigger = event.target.closest('.ffp-more-trigger');
			if (moreTrigger && this.root.contains(moreTrigger)) {
				event.preventDefault();
				this.toggleOverflow();
				return;
			}

			if (event.target.closest('.ffp-mobile-filter-trigger, .ffp-tablet-select-trigger')) {
				event.preventDefault();
				this.openSheet();
				return;
			}

			if (event.target.closest('.ffp-sheet__close') || event.target === this.backdrop) {
				event.preventDefault();
				this.closeSheet();
				return;
			}

			if (event.target.closest('.ffp-sheet__apply')) {
				event.preventDefault();
				const checked = this.sheet ? this.sheet.querySelector('input[type="radio"]:checked') : null;
				this.pendingTerm = checked ? Number(checked.value || 0) : 0;
				this.selectTerm(this.pendingTerm, true);
				this.closeSheet();
				return;
			}

			if (event.target.closest('.ffp-sheet__clear')) {
				event.preventDefault();
				this.pendingTerm = 0;
				this.syncSheetRadios(0);
				this.selectTerm(0, true);
				this.closeSheet();
				return;
			}

			const pageButton = event.target.closest('.ffp-page[data-page]');
			if (pageButton && this.root.contains(pageButton)) {
				event.preventDefault();
				this.loadPosts(Number(pageButton.dataset.page || 1), false, true);
				return;
			}

			const loadMore = event.target.closest('.ffp-load-more[data-page]');
			if (loadMore && this.root.contains(loadMore)) {
				event.preventDefault();
				this.loadPosts(Number(loadMore.dataset.page || 2), true, false);
			}
		}

		handleKeydown(event) {
			if (event.key === 'Escape') {
				if (this.sheet && !this.sheet.hidden) {
					this.closeSheet();
				} else {
					this.closeOverflow();
				}
				return;
			}

			if (event.key === 'Tab' && this.sheet && !this.sheet.hidden) {
				this.trapFocus(event);
			}
		}

		arrangeFilters() {
			const mode = this.updateResponsiveMode();

			if (mode === 'mobile') {
				this.resetDesktopChipVisibility();
				this.hideDesktopOverflow();
				this.arrangeMobileQuickFilters();
				return;
			}

			if (this.mobileQuickContainer) {
				this.mobileQuickContainer.hidden = true;
			}

			if (mode === 'tablet' && this.tabletLayout !== 'auto-fit') {
				this.resetDesktopChipVisibility();
				this.hideDesktopOverflow();
				return;
			}

			if (!this.moreButton || !this.overflowMenu || !this.termButtons.length || !this.filterChips) {
				return;
			}

			const maximum = mode === 'tablet'
				? Math.max(1, Number(this.root.dataset.tabletVisibleFilters || 6))
				: Number.POSITIVE_INFINITY;

			this.fitFiltersToWidth(maximum);
		}

		resetDesktopChipVisibility() {
			this.termButtons.forEach((button) => button.classList.remove('is-overflow-hidden'));
		}

		hideDesktopOverflow() {
			if (this.moreButton) {
				this.moreButton.hidden = true;
			}
			if (this.overflowMenu) {
				this.overflowMenu.replaceChildren();
			}
			this.closeOverflow();
		}


		arrangeMobileQuickFilters() {
			if (!this.mobileQuickContainer || !this.mobileQuickButtons.length) {
				return;
			}

			if (this.mobileLayout === 'filter-all-only') {
				this.mobileQuickButtons.forEach((button) => { button.hidden = true; });
				this.mobileQuickContainer.hidden = true;
				return;
			}

			this.mobileQuickContainer.hidden = false;
			const includeAll = this.mobileLayout === 'swipe-only';
			const candidates = this.mobileQuickButtons.filter((button) => includeAll || Number(button.dataset.term || 0) !== 0);
			this.mobileQuickButtons
				.filter((button) => !candidates.includes(button))
				.forEach((button) => { button.hidden = true; });

			const limit = (this.mobileLayout === 'quick-scroll' || this.mobileLayout === 'swipe-only')
				? candidates.length
				: Math.max(0, Number(this.root.dataset.mobileVisibleFilters || 3));
			const ordered = candidates.slice().sort((first, second) => {
				const firstActive = Number(first.dataset.term || 0) === this.activeTerm;
				const secondActive = Number(second.dataset.term || 0) === this.activeTerm;
				if (firstActive !== secondActive) {
					return firstActive ? -1 : 1;
				}
				return Number(first.dataset.termIndex || 0) - Number(second.dataset.termIndex || 0);
			});
			const visibleLimit = this.activeTerm !== 0 && limit === 0 ? 1 : limit;

			ordered.forEach((button, index) => {
				button.hidden = index >= visibleLimit;
				this.mobileQuickContainer.appendChild(button);
			});
		}

		fitFiltersToWidth(maximumVisible) {
			this.resetDesktopChipVisibility();
			this.closeOverflow();
			this.overflowMenu.replaceChildren();
			this.moreButton.hidden = false;
			this.moreButton.style.visibility = 'hidden';

			const available = Math.floor(this.filterChips.clientWidth || (this.filterInner ? this.filterInner.clientWidth : 0) || 0);
			const style = window.getComputedStyle(this.filterChips);
			const gap = parseFloat(style.columnGap || style.gap || '0') || 0;
			const allWidth = this.allButton ? this.allButton.getBoundingClientRect().width : 0;
			const moreWidth = this.moreButton.getBoundingClientRect().width;
			const termWidths = new Map(this.termButtons.map((button) => [button, button.getBoundingClientRect().width]));
			const totalButtons = (this.allButton ? 1 : 0) + this.termButtons.length;
			const totalWidth = allWidth
				+ this.termButtons.reduce((sum, button) => sum + (termWidths.get(button) || 0), 0)
				+ Math.max(0, totalButtons - 1) * gap;
			const countFits = this.termButtons.length <= maximumVisible;

			if (available > 0 && totalWidth <= available && countFits) {
				this.moreButton.hidden = true;
				this.moreButton.style.visibility = '';
				return;
			}

			const visible = new Set();
			const activeButton = this.termButtons.find((button) => Number(button.dataset.term || 0) === this.activeTerm);
			let used = allWidth + moreWidth + gap;
			let visibleCount = 0;

			const canAdd = (button) => {
				if (visibleCount >= maximumVisible) {
					return false;
				}
				const width = termWidths.get(button) || 0;
				return available <= 0 || used + width + gap <= available;
			};

			if (activeButton && canAdd(activeButton)) {
				visible.add(activeButton);
				used += (termWidths.get(activeButton) || 0) + gap;
				visibleCount += 1;
			}

			this.termButtons.forEach((button) => {
				if (button === activeButton || !canAdd(button)) {
					return;
				}
				visible.add(button);
				used += (termWidths.get(button) || 0) + gap;
				visibleCount += 1;
			});

			const overflowed = [];
			this.termButtons.forEach((button) => {
				const hidden = !visible.has(button);
				button.classList.toggle('is-overflow-hidden', hidden);
				if (hidden) {
					overflowed.push(button);
				}
			});

			overflowed.forEach((button) => {
				const item = document.createElement('button');
				item.type = 'button';
				item.className = 'ffp-overflow-item';
				item.dataset.term = button.dataset.term;
				item.setAttribute('role', 'menuitem');
				Array.from(button.childNodes).forEach((node) => item.appendChild(node.cloneNode(true)));
				if (Number(button.dataset.term || 0) === this.activeTerm) {
					item.classList.add('is-active');
				}
				this.overflowMenu.appendChild(item);
			});

			this.moreButton.hidden = overflowed.length === 0;
			this.moreButton.style.visibility = '';

			if (!overflowed.length) {
				this.closeOverflow();
			}
		}

		toggleOverflow() {
			if (!this.moreButton || !this.overflowMenu || this.moreButton.hidden) {
				return;
			}

			const opening = this.overflowMenu.hidden;
			this.overflowMenu.hidden = !opening;
			this.moreButton.setAttribute('aria-expanded', opening ? 'true' : 'false');

			if (opening) {
				this.positionOverflowMenu();
				window.requestAnimationFrame(() => this.positionOverflowMenu());
				const first = this.overflowMenu.querySelector('button');
				if (first) {
					first.focus({ preventScroll: true });
				}
			}
		}

		closeOverflow() {
			if (!this.moreButton || !this.overflowMenu) {
				return;
			}
			this.overflowMenu.hidden = true;
			this.moreButton.setAttribute('aria-expanded', 'false');
		}

		selectTerm(termId, shouldLoad) {
			this.activeTerm = Number.isFinite(termId) ? termId : 0;
			this.pendingTerm = this.activeTerm;
			this.syncSelection();
			this.closeOverflow();
			this.returnMobileFiltersToStart();

			if (shouldLoad) {
				this.loadPosts(1, false, false);
			}
		}

		returnMobileFiltersToStart() {
			if (!this.mobileQuickContainer || this.currentMode !== 'mobile') {
				return;
			}

			// Preserve the page's vertical position. Some mobile browsers can move the
			// document while a nested horizontal scroller is reordered or animated.
			const pageScrollY = window.scrollY;

			// The selected category is reordered to the first position during layout.
			// Use an immediate horizontal reset instead of smooth scrolling to avoid
			// competing with an in-progress vertical touch gesture on phones.
			window.requestAnimationFrame(() => {
				this.arrangeMobileQuickFilters();
				window.requestAnimationFrame(() => {
					if (!this.mobileQuickContainer) {
						return;
					}

					this.mobileQuickContainer.scrollLeft = 0;

					// Correct only browser-induced movement caused by the nested scroller.
					// This runs immediately after a category tap, not during normal swiping.
					if (Math.abs(window.scrollY - pageScrollY) > 2) {
						window.scrollTo(0, pageScrollY);
					}
				});
			});
		}

		syncSelection() {
			const buttons = Array.from(this.root.querySelectorAll('.ffp-chip[data-term], .ffp-overflow-item[data-term]'));
			buttons.forEach((button) => {
				const active = Number(button.dataset.term || 0) === this.activeTerm;
				button.classList.toggle('is-active', active);
				if (button.classList.contains('ffp-chip')) {
					button.setAttribute('aria-pressed', active ? 'true' : 'false');
				}
			});

			this.syncSheetRadios(this.activeTerm);
			this.updateResponsiveLabels();
			this.scheduleLayout();
		}

		getActiveLabel() {
			const source = this.root.querySelector(`.ffp-filter-chips .ffp-chip[data-term="${this.activeTerm}"]`)
				|| this.root.querySelector(`.ffp-mobile-quick-chip[data-term="${this.activeTerm}"]`);
			return source?.dataset.label || source?.querySelector('.ffp-chip__label')?.textContent?.trim() || this.allLabel;
		}

		updateResponsiveLabels() {
			const label = this.getActiveLabel();
			const prefix = this.mobileActivePrefix.trim();
			const displayLabel = prefix ? `${prefix} ${label}` : label;

			if (this.tabletActiveLabel) {
				this.tabletActiveLabel.textContent = displayLabel;
			}
			if (this.tabletSelectTrigger) {
				this.tabletSelectTrigger.setAttribute('aria-label', this.formatString(window.FilterFlowPosts?.i18n?.currentCategory || 'Current category: %s. Open category filters.', label));
			}
			if (this.mobileAllButton) {
				this.mobileAllButton.setAttribute(
					'aria-label',
					this.activeTerm === 0
						? this.formatString(window.FilterFlowPosts?.i18n?.allSelected || '%s, selected.', this.allLabel)
						: this.formatString(window.FilterFlowPosts?.i18n?.showAll || 'Show %s posts.', this.allLabel)
				);
			}
		}

		formatString(template, value) {
			return String(template).replace('%s', String(value));
		}

		syncSheetRadios(termId) {
			if (!this.sheet) {
				return;
			}
			const target = this.sheet.querySelector(`input[type="radio"][value="${termId}"]`);
			if (target) {
				target.checked = true;
			}
		}

		openSheet() {
			if (!this.sheet || !this.backdrop) {
				return;
			}

			this.lastFocused = document.activeElement;
			this.refreshFromDom(false);
			this.syncPortalTheme();
			this.pendingTerm = this.activeTerm;
			this.syncSheetRadios(this.activeTerm);
			this.sheet.hidden = false;
			this.backdrop.hidden = false;
			document.body.classList.add('ffp-sheet-open');
			this.sheet.style.setProperty('--ffp-visual-height', `${Math.round(window.visualViewport?.height || window.innerHeight)}px`);

			[this.mobileTrigger, this.tabletSelectTrigger].forEach((trigger) => {
				if (trigger) {
					trigger.setAttribute('aria-expanded', 'true');
				}
			});

			window.requestAnimationFrame(() => {
				const close = this.sheet.querySelector('.ffp-sheet__close');
				if (close) {
					close.focus();
				}
			});
		}

		closeSheet() {
			if (!this.sheet || !this.backdrop) {
				return;
			}

			this.sheet.hidden = true;
			this.backdrop.hidden = true;
			document.body.classList.remove('ffp-sheet-open');

			[this.mobileTrigger, this.tabletSelectTrigger].forEach((trigger) => {
				if (trigger) {
					trigger.setAttribute('aria-expanded', 'false');
				}
			});

			if (this.lastFocused && typeof this.lastFocused.focus === 'function') {
				this.lastFocused.focus();
			}
		}

		trapFocus(event) {
			const focusable = Array.from(
				this.sheet.querySelectorAll('button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])')
			).filter((element) => element.offsetParent !== null);

			if (!focusable.length) {
				return;
			}

			const first = focusable[0];
			const last = focusable[focusable.length - 1];

			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		}

		applyHeaderCollisionGuard() {
			if (!this.filterBar) {
				return;
			}

			if (!this.headerCollisionGuard) {
				this.setRootProperty('--ffp-auto-header-clearance', '0px');
				this.setRootProperty('--ffp-auto-sticky-top', '0px');
				return;
			}

			const rootRect = this.root.getBoundingClientRect();
			const metrics = this.measureHeaderBounds(rootRect);
			const clearance = Math.min(420, Math.max(0, Math.ceil(metrics.normalClearance || 0)));
			const stickyTop = metrics.stickyBottom > 0
				? Math.min(420, Math.max(0, Math.ceil(metrics.stickyBottom + this.headerCollisionGap)))
				: 0;

			this.setRootProperty('--ffp-auto-header-clearance', `${clearance}px`);
			this.setRootProperty('--ffp-auto-sticky-top', `${stickyTop}px`);
		}

		scheduleStickyHeaderOffset() {
			if (!this.headerCollisionGuard || !this.filterBar || this.stickyHeaderFrame) {
				return;
			}

			this.stickyHeaderFrame = window.requestAnimationFrame(() => {
				this.stickyHeaderFrame = 0;
				const metrics = this.measureHeaderBounds(this.root.getBoundingClientRect());
				const stickyTop = metrics.stickyBottom > 0
					? Math.min(420, Math.max(0, Math.ceil(metrics.stickyBottom + this.headerCollisionGap)))
					: 0;
				this.setRootProperty('--ffp-auto-sticky-top', `${stickyTop}px`);
			});
		}

		getHeaderCandidates() {
			const selectors = [
				'#wpadminbar',
				'header',
				'#masthead',
				'.site-header',
				'.elementor-location-header',
				'[data-elementor-type="header"]',
				'nav',
				'.site-navigation',
				'.elementor-widget-nav-menu',
				'.elementor-nav-menu--main',
				'.elementor-nav-menu--dropdown',
				'.e-n-menu',
				'.e-n-menu-wrapper'
			];

			return Array.from(new Set(document.querySelectorAll(selectors.join(','))));
		}

		measureHeaderBounds(targetRect) {
			const candidates = this.getHeaderCandidates();
			let normalClearance = 0;
			let stickyBottom = 0;
			const rootDocumentTop = targetRect.top + window.scrollY;

			candidates.forEach((candidate) => {
				if (candidate === this.root || this.root.contains(candidate) || candidate.contains(this.root)) {
					return;
				}

				const relation = candidate.compareDocumentPosition(this.root);
				if (!(relation & Node.DOCUMENT_POSITION_FOLLOWING)) {
					return;
				}

				const style = window.getComputedStyle(candidate);
				if (style.display === 'none' || style.visibility === 'hidden' || Number(style.opacity) === 0) {
					return;
				}

				const rect = candidate.getBoundingClientRect();
				const overlapsHorizontally = rect.right > targetRect.left && rect.left < targetRect.right;
				if (!overlapsHorizontally || rect.width < 1 || rect.height < 1 || rect.bottom <= 0) {
					return;
				}

				const isSticky = style.position === 'fixed'
					|| style.position === 'sticky'
					|| candidate.classList.contains('elementor-sticky--active');

				if (isSticky && rect.top < window.innerHeight && rect.bottom > 0) {
					stickyBottom = Math.max(stickyBottom, rect.bottom);
				}

				// Fixed/sticky headers are viewport overlays. They affect the sticky
				// offset, but must never change the widget's document-flow padding while
				// the user scrolls, otherwise mobile browsers can visibly jump.
				if (!isSticky) {
					const candidateDocumentTop = rect.top + window.scrollY;
					const candidateDocumentBottom = rect.bottom + window.scrollY;
					const collisionBandEnd = rootDocumentTop + Math.max(96, Math.min(360, targetRect.height));
					const overlapsContentStart = candidateDocumentTop < collisionBandEnd
						&& candidateDocumentBottom > rootDocumentTop;

					if (overlapsContentStart) {
						normalClearance = Math.max(
							normalClearance,
							candidateDocumentBottom + this.headerCollisionGap - rootDocumentTop
						);
					}
				}
			});

			return { normalClearance, stickyBottom };
		}

		setRootProperty(name, value) {
			if (this.root.style.getPropertyValue(name) !== value) {
				this.root.style.setProperty(name, value);
			}
		}

		async loadPosts(page, append, scrollToResults) {
			if (!this.grid || !this.pagination || !window.FilterFlowPosts) {
				return;
			}

			if (this.abortController) {
				this.abortController.abort();
			}
			this.abortController = new AbortController();

			this.root.classList.add('is-loading');
			if (this.results) {
				this.results.setAttribute('aria-busy', 'true');
			}
			this.announce(window.FilterFlowPosts.i18n?.loading || 'Loading posts…');

			const data = new FormData();
			data.append('action', 'filterflow_load_posts');
			data.append('nonce', window.FilterFlowPosts.nonce || '');
			data.append('settings', JSON.stringify(this.settings));
			data.append('term_id', String(this.activeTerm));
			data.append('page', String(Math.max(1, page)));

			try {
				const response = await fetch(window.FilterFlowPosts.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: data,
					signal: this.abortController.signal
				});

				if (!response.ok) {
					throw new Error(`HTTP ${response.status}`);
				}

				const payload = await response.json();
				if (!payload.success || !payload.data) {
					throw new Error(payload.data?.message || 'Invalid response');
				}

				// Replacing a large result grid can trigger browser scroll anchoring on
				// phones. Capture the current viewport position immediately before the
				// synchronous DOM update and restore it for filter changes. Pagination
				// keeps its explicit scroll-to-results behaviour.
				const preservePagePosition = !append && !scrollToResults;
				const pageScrollYBeforeUpdate = preservePagePosition ? window.scrollY : 0;

				if (append) {
					const fragment = document.createRange().createContextualFragment(payload.data.html || '');
					this.grid.appendChild(fragment);
				} else {
					this.grid.replaceChildren(document.createRange().createContextualFragment(payload.data.html || ''));
				}

				this.pagination.replaceChildren(document.createRange().createContextualFragment(payload.data.pagination || ''));

				if (preservePagePosition && Math.abs(window.scrollY - pageScrollYBeforeUpdate) > 2) {
					window.scrollTo(0, pageScrollYBeforeUpdate);
				}
				const foundPosts = Number(payload.data.foundPosts || 0);
				const foundTemplate = foundPosts === 1
					? (window.FilterFlowPosts.i18n?.postFound || '%d post found.')
					: (window.FilterFlowPosts.i18n?.postsFound || '%d posts found.');
				this.announce(foundTemplate.replace('%d', String(foundPosts)));

				if (scrollToResults && !append) {
					const top = this.root.getBoundingClientRect().top + window.scrollY - 24;
					window.scrollTo({ top, behavior: 'smooth' });
				}
			} catch (error) {
				if (error.name !== 'AbortError') {
					this.announce(window.FilterFlowPosts.i18n?.error || 'The posts could not be loaded. Please try again.');
					this.showTemporaryError();
				}
			} finally {
				this.root.classList.remove('is-loading');
				if (this.results) {
					this.results.setAttribute('aria-busy', 'false');
				}
			}
		}

		announce(message) {
			if (!this.status) {
				return;
			}
			this.status.textContent = '';
			window.setTimeout(() => {
				this.status.textContent = message;
			}, 30);
		}

		showTemporaryError() {
			if (!this.results || this.results.querySelector('.ffp-error')) {
				return;
			}

			const error = document.createElement('div');
			error.className = 'ffp-error';
			error.setAttribute('role', 'alert');
			error.textContent = window.FilterFlowPosts.i18n?.error || 'The posts could not be loaded. Please try again.';
			this.results.prepend(error);
			window.setTimeout(() => error.remove(), 4500);
		}
	}

	function initWithin(scope) {
		const roots = scope?.matches?.('.ffp-widget')
			? [scope]
			: Array.from(scope?.querySelectorAll?.('.ffp-widget') || []);

		roots.forEach((root) => {
			if (root.__filterFlowInstance) {
				root.__filterFlowInstance.refreshFromDom();
			} else {
				new FilterFlowWidget(root);
			}
		});
	}

	function initAll() {
		document.querySelectorAll('.ffp-widget').forEach((root) => {
			if (root.__filterFlowInstance) {
				root.__filterFlowInstance.refreshFromDom();
			} else {
				new FilterFlowWidget(root);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}

	window.addEventListener('elementor/frontend/init', function () {
		if (window.elementorFrontend?.hooks) {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/filterflow_posts.default', function ($scope) {
				const scope = $scope && $scope[0] ? $scope[0] : $scope;
				initWithin(scope);
			});
		}
	});
})();
