
// Initialize Google Sign-In

function initGoogleSignIn() {

    // Wait for Google Identity Services to load

    if (typeof google !== 'undefined' && google.accounts && window.GOOGLE_CLIENT_ID && window.GOOGLE_CLIENT_ID !== 'YOUR_GOOGLE_CLIENT_ID_HERE') {
        google.accounts.id.initialize({
            client_id: window.GOOGLE_CLIENT_ID,
            callback: handleCredentialResponse
        });

        // Render button if element exists (on login page)

        const signInButton = document.getElementById("google-signin-button");
        if (signInButton) {
            google.accounts.id.renderButton(
                signInButton,
                { theme: "outline", size: "large", width: "100%" }
            );
        }
    } else if (window.GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID_HERE') {

        // Show error if Client ID is not configured

        const signInButton = document.getElementById("google-signin-button");
        if (signInButton) {
            signInButton.innerHTML = '<div style="padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px; text-align: center;">Please configure Google Client ID in config/config.php</div>';
        }
    }
}

// Initialize on load

window.onload = function() {
    initGoogleSignIn();
};

//initializing after a delay
setTimeout(function() {
    if (typeof google === 'undefined' || !google.accounts) {
        initGoogleSignIn();
    }
}, 1000);

function handleCredentialResponse(response) {

    // loading state

    const signInButton = document.getElementById("google-signin-button");
    if (signInButton) {
        signInButton.style.opacity = '0.6';
        signInButton.style.pointerEvents = 'none';
    }

    // Base URL so fetch works from any page (e.g. login.php or /jobfinding/login.php)
    
    var base = window.location.href.replace(/\/[^/]*$/, '');
    if (base.indexOf('/jobfinding') === -1 && window.location.pathname.indexOf('/jobfinding') !== -1) {
        base = window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '');
    }
    var callbackUrl = base + '/auth/google_callback.php';

    fetch(callbackUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ credential: response.credential })
    })
    .then(function(response) {
        var contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            return response.text().then(function(text) {
                throw new Error(text || 'Server returned an error. Run init_database.php if you have not yet.');
            });
        }
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            var urlParams = new URLSearchParams(window.location.search);
            var redirect = urlParams.get('redirect') || 'index.php';
            window.location.href = redirect;
        } else {
            alert('Login failed: ' + (data.message || 'Unknown error'));
            if (signInButton) {
                signInButton.style.opacity = '1';
                signInButton.style.pointerEvents = 'auto';
            }
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        var msg = (error && error.message) ? error.message : 'An error occurred during login. Please try again.';
        if (msg.length > 200) msg = msg.substring(0, 200) + '...';
        alert(msg);
        if (signInButton) {
            signInButton.style.opacity = '1';
            signInButton.style.pointerEvents = 'auto';
        }
    });
}

// Filter functionality
function filterCategory(category) {
    const url = new URL(window.location);
    if (category === 'all') {
        url.searchParams.delete('category');
    } else {
        url.searchParams.set('category', category);
    }
    window.location.href = url.toString();
}

// Card click handler - navigate to job details when clicking card
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.job-card');
    cards.forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function(e) {
            // Don't navigate if clicking on a link inside the card
            if (e.target.tagName === 'A' || e.target.closest('a')) {
                return;
            }
            const jobId = this.dataset.jobId;
            const table = this.dataset.table;
            if (jobId) {
                let url = 'ad-detail.php?id=' + encodeURIComponent(jobId);
                if (table) {
                    url += '&table=' + encodeURIComponent(table);
                }
                window.location.href = url;
            }
        });
    });
});
