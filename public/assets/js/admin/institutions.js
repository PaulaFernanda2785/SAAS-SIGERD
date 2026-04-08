(() => {
  const openModal = (element) => {
    if (!element) {
      return;
    }

    element.removeAttribute('hidden');
    element.classList.add('is-open');
  };

  const closeModal = (element) => {
    if (!element) {
      return;
    }

    element.classList.remove('is-open');
    element.setAttribute('hidden', 'hidden');
  };

  const initTabs = () => {
    const shell = document.querySelector('[data-institution-tabs]');
    if (!shell) {
      return;
    }

    const triggers = Array.from(shell.querySelectorAll('[data-tab-trigger]'));
    const panels = Array.from(shell.querySelectorAll('[data-tab-panel]'));
    if (triggers.length === 0 || panels.length === 0) {
      return;
    }

    const firstTab = triggers[0].getAttribute('data-tab-trigger') || 'contas';
    const setActive = (tab) => {
      const resolvedTab = triggers.some(
        (trigger) => trigger.getAttribute('data-tab-trigger') === tab
      )
        ? tab
        : firstTab;

      triggers.forEach((trigger) => {
        const selected = trigger.getAttribute('data-tab-trigger') === resolvedTab;
        trigger.classList.toggle('is-active', selected);
        trigger.setAttribute('aria-selected', selected ? 'true' : 'false');
      });

      panels.forEach((panel) => {
        panel.classList.toggle('is-active', panel.getAttribute('data-tab-panel') === resolvedTab);
      });
    };

    setActive(shell.getAttribute('data-active-tab') || firstTab);

    triggers.forEach((trigger) => {
      trigger.addEventListener('click', (event) => {
        event.preventDefault();
        setActive(trigger.getAttribute('data-tab-trigger') || firstTab);
      });
    });
  };

  const initDeleteModal = () => {
    const modal = document.querySelector('[data-delete-modal]');
    if (!modal) {
      return;
    }

    const inputEntity = modal.querySelector('[data-delete-entity]');
    const inputEntityId = modal.querySelector('[data-delete-entity-id]');
    const inputTab = modal.querySelector('[data-delete-tab]');
    const label = modal.querySelector('[data-delete-entity-label]');
    if (!inputEntity || !inputEntityId || !inputTab || !label) {
      return;
    }

    document.querySelectorAll('[data-delete-open]').forEach((button) => {
      button.addEventListener('click', (event) => {
        event.preventDefault();
        inputEntity.value = button.getAttribute('data-entity') || '';
        inputEntityId.value = button.getAttribute('data-entity-id') || '';
        inputTab.value = button.getAttribute('data-tab') || 'contas';
        label.textContent = button.getAttribute('data-entity-label') || 'Item selecionado';
        openModal(modal);
      });
    });

    modal.querySelectorAll('[data-delete-close]').forEach((button) => {
      button.addEventListener('click', (event) => {
        event.preventDefault();
        closeModal(modal);
      });
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && modal.classList.contains('is-open')) {
        closeModal(modal);
      }
    });
  };

  const initProfileGuideModal = () => {
    const modal = document.querySelector('[data-profile-guide-modal]');
    if (!modal) {
      return;
    }

    document.querySelectorAll('[data-profile-guide-open]').forEach((button) => {
      button.addEventListener('click', (event) => {
        event.preventDefault();
        openModal(modal);
      });
    });

    modal.querySelectorAll('[data-profile-guide-close]').forEach((button) => {
      button.addEventListener('click', (event) => {
        event.preventDefault();
        closeModal(modal);
      });
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && modal.classList.contains('is-open')) {
        closeModal(modal);
      }
    });
  };

  const init = () => {
    initTabs();
    initDeleteModal();
    initProfileGuideModal();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
    return;
  }

  init();
})();
