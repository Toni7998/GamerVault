document.addEventListener("DOMContentLoaded", loadLists);

function loadLists() {
    fetch("/api/lists")
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById("lists-container");
            container.innerHTML = '';
            data.forEach(list => {
                const el = document.createElement("div");
                el.classList.add("list-card");
                el.innerHTML = `<h3>${list.name}</h3>`;
                container.appendChild(el);
            });
        });
}

function showNewListForm() {
    document.getElementById("new-list-form").style.display = 'block';
}

function createList() {
    const name = document.getElementById("newListName").value;
    fetch("/api/lists", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name })
    })
        .then(() => {
            loadLists();
            document.getElementById("newListName").value = '';
            document.getElementById("new-list-form").style.display = 'none';
        });
}
