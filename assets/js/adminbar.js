/* global millicache */

import '../css/adminbar.scss';

document.addEventListener('DOMContentLoaded', () => {
    const adminbar = document.getElementById('wp-admin-bar-millicache');
    if (!adminbar) return;

    // Replace admin bar links with buttons for correct semantics.
    adminbar.querySelectorAll('a.ab-item').forEach(link => {
        const url = new URL(link.href, window.location.origin);

        // Check for millicache action
        const millicacheAction = url.searchParams.get('_millicache');
        if (!millicacheAction) return;

        // Create the button to replace the link
        const button = document.createElement('button');
        button.className = 'ab-item';
        button.innerHTML = link.innerHTML;
        button.dataset.action = millicacheAction;
        button.dataset.targets = url.searchParams.get('_targets') || '';

        link.parentNode.replaceChild(button, link);
    });


    // Add AJAX event listeners to admin bar buttons.
    adminbar.querySelectorAll('button.ab-item').forEach(button => {
        button.addEventListener('click', e => {
            e.preventDefault();

            if (button.classList.contains('disabled')) return;

            const mainButton = button.closest('#wp-admin-bar-millicache');
            const action = button.dataset.action;

            if (action) {
                try {
                    mainButton.classList.add('flushing');
                    button.classList.add('disabled');
                    clearCache(action, button.dataset.targets ? button.dataset.targets.split(',') : null);
                } finally {
                    setTimeout(() => {
                        mainButton.classList.remove('flushing');
                        button.classList.remove('disabled');
                    }, 750);
                }
            }
        });
    });
});

function clearCache(action, targets = null) {
    // Determine the endpoint based on the action value.
    const endpoint = action.startsWith('clear') ? 'cache' : 'action';

    // Prepare the data for the REST API request.
    const data = { action };

    // Add action-specific parameters.
    if (action === 'clear') {
        data.is_network_admin = millicache.is_network_admin;
    } else if (action === 'clear_current') {
        data.request_flags = millicache.request_flags;
    } else if (action === 'clear_targets' && targets) {
        data.targets = targets;
    }

    // Make the REST API request using wp.apiFetch.
    wp.apiFetch({
        path: `/millicache/v1/${endpoint}`,
        method: 'POST',
        data: data
    })
        .then(result => {
            showNotice(result.message, result.success ? 'success' : 'error');
        })
        .catch(error => {
            console.error('Error:', error);
            showNotice(error.message || 'Error clearing cache', 'error');
        });
}

function showNotice(message, type) {
    console.log(`${type}: ${message}`);
}