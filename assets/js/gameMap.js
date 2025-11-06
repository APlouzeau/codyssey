document.querySelectorAll(".level").forEach(level => {
    level.addEventListener("click", () => {
        let id = level.dataset.level;
        console.log("Level selected:", id);

        // redirection future ?
        // window.location.href = "/level/" + id;

        movePlayerTo(level);
    });
});

function movePlayerTo(target) {
    const player = document.getElementById("player");
    const rect = target.getBoundingClientRect();

    player.style.transition = "0.5s ease";
    player.style.left = rect.left + "px";
    player.style.top = rect.top + "px";
}
