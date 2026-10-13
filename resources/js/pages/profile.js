import { h } from '../dom';

function avatar(user) {
    return user.avatar_url
        ? h('img', { src: user.avatar_url, alt: `${user.name}'s profile picture`, class: 'size-14 shrink-0 rounded-full object-cover' })
        : h(
              'span',
              {
                  'aria-label': `${user.name}'s initials`,
                  class: 'text-brand grid size-14 shrink-0 place-items-center rounded-full bg-blue-100 text-lg font-semibold uppercase',
              },
              user.initials,
          );
}

function initProfile(form) {
    const render = (user) => {
        form.querySelector('[data-avatar]')?.replaceChildren(avatar(user));
        const removeAvatar = form.querySelector('[data-remove-avatar]');
        removeAvatar.hidden = !user.avatar_url;
        removeAvatar.querySelector('input').checked = false;
        form.elements.namedItem('avatar').value = '';
    };

    form.addEventListener('ajax:filled', (event) => render(event.detail));
    form.addEventListener('ajax:success', (event) => event.detail.data && render(event.detail.data));
}

document.querySelectorAll('[data-profile-form]').forEach(initProfile);
