document.addEventListener("DOMContentLoaded", loadLists);

function loadLists() {
    fetch("/api/lists")
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById("lists-container");
            container.innerHTML = '';

            if (data.length === 0) {
                container.innerHTML = "<p class='text-gray-600'>No tens cap llista creada encara. 🗂️</p>";
                return;
            }

            data.forEach(list => {
                const el = document.createElement("div");
                el.classList.add("list-card", "p-4", "border", "rounded", "shadow", "hover:shadow-lg", "transition", "bg-white");
                el.innerHTML = `<h3 class="font-semibold text-lg">${list.name}</h3>`;
                container.appendChild(el);
            });
        })
        .catch(error => {
            console.error("Error carregant les llistes:", error);
        });
}

function showNewListForm() {
    document.getElementById("new-list-form").classList.toggle('hidden');
}

function createList() {
    const name = document.getElementById("newListName").value.trim();
    if (!name) return;

    fetch("/api/lists", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name })
    })
        .then(response => {
            if (!response.ok) throw new Error("Error creant la llista.");
            return response.json();
        })
        .then(() => {
            loadLists();
            document.getElementById("newListName").value = '';
            document.getElementById("new-list-form").classList.add('hidden');
        })
        .catch(error => {
            console.error("Error creant la llista:", error);
        });
}
