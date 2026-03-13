function filtrer(categorie, event) {
    const produits = document.querySelectorAll(".produit");
    const titres = document.querySelectorAll(".categorie-titre");

    if (categorie === "tous") {
        produits.forEach(p => p.style.display = "block");
        titres.forEach(t => t.style.display = "block");
    } else {
        produits.forEach(p => p.style.display = p.classList.contains(categorie) ? "block" : "none");

        titres.forEach(t => {
            const texte = t.textContent.toLowerCase();
            if (texte.includes(categorie)) {
                t.style.display = "block";
            } else if (categorie === "sale" && texte.includes("salé")) {
                t.style.display = "block";
            } else {
                t.style.display = "none";
            }
        });
    }

    // Gestion du bouton actif
    const boutons = document.querySelectorAll(".filtres button");
    boutons.forEach(btn => btn.classList.remove("actif"));
    if(event) event.target.classList.add("actif");
}