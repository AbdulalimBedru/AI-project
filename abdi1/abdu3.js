// Get references to game elements
const player = document.getElementById('player');
const obstacle = document.getElementById('obstacle');
const gameContainer = document.getElementById('game-container');

// Set initial positions
player.style.left = '100px';
player.style.top = '250px';
obstacle.style.left = '650px';
obstacle.style.top = '200px';

// Function to move the player
function movePlayer(event) {
    if (event.key === 'ArrowLeft') {
        player.style.left = (parseInt(player.style.left) - 10) + 'px';
    } else if (event.key === 'ArrowRight') {
        player.style.left = (parseInt(player.style.left) + 10) + 'px';
    }
}

// Event listener for keypresses
document.addEventListener('keydown', movePlayer);

// Game loop (using setInterval)
setInterval(() => {
    // Move the obstacle
    obstacle.style.left = (parseInt(obstacle.style.left) - 5) + 'px';

    // Check for collisions
    if (
        parseInt(player.style.left) + 50 >= parseInt(obstacle.style.left) &&
        parseInt(player.style.top) <= parseInt(obstacle.style.top) + 50 &&
        parseInt(player.style.top) + 50 >= parseInt(obstacle.style.top)
    ) {
        // Game over
        alert('Game Over!');
        clearInterval(gameLoop);
    }
}, 10);