// /organigramme/src/js/organigramme.js

// Affichage des métiers
document.addEventListener('DOMContentLoaded', function() {
    const modalOverlay = document.getElementById('modal-overlay');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');

    document.querySelectorAll('.show-jobs').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const jobsData = this.getAttribute('data-jobs');
            let jobs = [];
            try {
                jobs = JSON.parse(jobsData);
            } catch (err) {
                console.error(err);
            }
            let html = '<h3>Métiers</h3><ul>';
            jobs.forEach(job => {
                html += '<li>' + job.job + '</li>';
            });
            html += '</ul>';
            modalBody.innerHTML = html;
            modalOverlay.style.display = 'block';
        });
    });

    modalClose.addEventListener('click', function() {
        modalOverlay.style.display = 'none';
    });


    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            modalOverlay.style.display = 'none';
        }
    });
});