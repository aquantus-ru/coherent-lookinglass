document.addEventListener('DOMContentLoaded', function() {

    function escapeHtml(text) {
        if (text == null) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Polling function
    function loadNames() {
        // Load suggestions
        fetch('api.php?action=suggestions')
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('suggested-names-list');
                if (list) {
                    list.innerHTML = '';
                    if (data.length === 0) {
                        list.innerHTML = 'No suggestions yet.';
                        return;
                    }
                    const ul = document.createElement('ul');
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.innerHTML = `<a href="babynames.php?id=${item.id}"><b>${escapeHtml(item.name)}</b></a>: ${escapeHtml(item.definition)}`;
                        ul.appendChild(li);
                    });
                    list.appendChild(ul);
                }
            });

        // Load all names
        fetch('api.php?action=list')
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('all-names-list');
                const sidebarList = document.getElementById('sidebar-recent-names');

                if (list) {
                    list.innerHTML = '';
                    if (data.length === 0) {
                        list.innerHTML = 'No names found.';
                    } else {
                        const ul = document.createElement('ul');
                        data.forEach(item => {
                            const li = document.createElement('li');
                            li.innerHTML = `<a href="babynames.php?id=${item.id}"><b>${escapeHtml(item.name)}</b></a>: ${escapeHtml(item.definition)}`;
                            ul.appendChild(li);
                        });
                        list.appendChild(ul);
                    }
                }

                if (sidebarList) {
                    sidebarList.innerHTML = '';
                     if (data.length === 0) {
                        sidebarList.innerHTML = 'Empty.';
                    } else {
                        const ul = document.createElement('ul');
                        ul.className = 'sidebar-nav-list';
                        // Show top 5
                        data.slice(0, 5).forEach(item => {
                            const li = document.createElement('li');
                            li.innerHTML = `<a href="babynames.php?id=${item.id}">${escapeHtml(item.name)}</a>`;
                            ul.appendChild(li);
                        });
                        sidebarList.appendChild(ul);
                    }
                }
            });
    }

    // Submit form
    const form = document.getElementById('submit-name-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const data = {
                name: formData.get('name'),
                definition: formData.get('definition')
            };

            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                const msg = document.getElementById('submit-message');
                if (result.success) {
                    msg.innerHTML = '<span style="color:green">Name submitted successfully! Waiting for approval.</span>';
                    form.reset();
                    loadNames(); // Refresh lists
                } else {
                    msg.innerHTML = '<span style="color:red">Error: ' + result.error + '</span>';
                }
            });
        });
    }

    // Autocomplete
    const searchInput = document.getElementById('search_input');
    const resultsContainer = document.getElementById('autocomplete-results');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value;
            if (query.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }

            fetch(`api.php?action=search&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.length > 0) {
                        resultsContainer.style.display = 'block';
                        const ul = document.createElement('ul');
                        ul.style.listStyle = 'none';
                        ul.style.padding = '5px';
                        ul.style.margin = '0';

                        data.forEach(item => {
                            const li = document.createElement('li');
                            li.style.padding = '3px';
                            li.style.cursor = 'pointer';
                            li.innerHTML = `<b>${escapeHtml(item.name)}</b> - <span class="small-text">${escapeHtml(item.definition.substring(0, 30))}...</span>`;
                            li.addEventListener('click', () => {
                                window.location.href = `babynames.php?id=${item.id}`;
                            });
                            li.onmouseover = function() { this.style.backgroundColor = '#eef'; };
                            li.onmouseout = function() { this.style.backgroundColor = '#fff'; };
                            ul.appendChild(li);
                        });
                        resultsContainer.appendChild(ul);
                    } else {
                        resultsContainer.style.display = 'none';
                    }
                });
        });

        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== searchInput) {
                resultsContainer.style.display = 'none';
            }
        });
    }

    // Initial load
    loadNames();

    // Poll every 30 seconds
    setInterval(loadNames, 30000);
});
