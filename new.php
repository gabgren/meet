<?php
// Generate memorable room names using word combinations
function generateRoomName() {
    // Extensive lists of short, memorable words

    $adjectives = [
        'happy', 'bright', 'quick', 'smart', 'cool', 'warm', 'bold', 'calm',
        'fast', 'wise', 'kind', 'brave', 'pure', 'true', 'free', 'wild',
        'soft', 'hard', 'deep', 'high', 'low', 'big', 'small', 'new', 'old',
        'red', 'blue', 'green', 'gold', 'silver', 'dark', 'light', 'clear',
        'gentle', 'fierce', 'silent', 'noisy', 'shy', 'proud', 'sweet', 'sharp',
        'smooth', 'rough', 'bright', 'dull', 'rich', 'poor', 'young', 'ancient',
        'fresh', 'dry', 'wet', 'icy', 'hot', 'cold', 'lazy', 'eager', 'swift',
        'steady', 'lucky', 'graceful', 'cheerful', 'mighty', 'tiny', 'vast',
        'noble', 'humble', 'loyal', 'fancy', 'plain', 'gentle', 'stormy',
        'quiet', 'lively', 'glad', 'sad', 'jolly', 'silly', 'funny', 'serene',
        'stormy', 'dusty', 'frosty', 'crisp', 'spicy', 'bitter', 'sweet', 'sour',
        'zesty', 'spiky', 'smooth', 'bumpy', 'crooked', 'straight', 'round',
        'square', 'oval', 'pointy', 'flat', 'hollow', 'solid', 'thin', 'thick',
        'wide', 'narrow', 'tall', 'short', 'long', 'brief', 'quick', 'slow',
        'rapid', 'gentle', 'rough', 'soft', 'firm', 'stiff', 'loose', 'tight'
    ];

    $nouns = [
        'cat', 'dog', 'bird', 'fish', 'tree', 'star', 'moon', 'sun', 'wind',
        'rain', 'snow', 'fire', 'water', 'earth', 'sky', 'sea', 'mountain',
        'river', 'lake', 'forest', 'desert', 'ocean', 'cloud', 'storm', 'wave',
        'rock', 'gem', 'crown', 'sword', 'shield', 'key', 'door', 'window',
        'flower', 'leaf', 'root', 'branch', 'seed', 'fruit', 'stone', 'shell',
        'island', 'valley', 'hill', 'meadow', 'field', 'path', 'road', 'bridge',
        'tower', 'castle', 'village', 'city', 'garden', 'harbor', 'beach',
        'cliff', 'cave', 'tunnel', 'gate', 'wall', 'fence', 'barn', 'well',
        'spring', 'brook', 'creek', 'pond', 'pool', 'canal', 'bay', 'reef',
        'ship', 'boat', 'raft', 'cart', 'wagon', 'train', 'plane', 'car',
        'bike', 'bus', 'track', 'trail', 'camp', 'tent', 'hut', 'cabin',
        'den', 'nest', 'web', 'burrow', 'hole', 'ring', 'coin', 'bell', 'drum',
        'flute', 'horn', 'book', 'scroll', 'map', 'chart', 'flag', 'banner',
        'cloak', 'robe', 'hat', 'mask', 'glove', 'boot', 'shoe', 'sock',
        'rope', 'chain', 'lock', 'box', 'bag', 'sack', 'jar', 'jug', 'cup',
        'bowl', 'plate', 'spoon', 'fork', 'knife', 'torch', 'lamp', 'candle',
        'lantern', 'mirror', 'brush', 'comb', 'clock', 'watch', 'bead', 'pearl'
    ];

    $verbs = [
        'run', 'jump', 'fly', 'swim', 'dance', 'sing', 'play', 'work', 'rest',
        'dream', 'hope', 'love', 'care', 'help', 'give', 'take', 'make',
        'build', 'create', 'find', 'seek', 'learn', 'teach', 'grow', 'bloom',
        'climb', 'crawl', 'slide', 'glide', 'spin', 'roll', 'walk', 'march',
        'skip', 'hop', 'race', 'chase', 'hide', 'seek', 'draw', 'paint',
        'write', 'read', 'count', 'cook', 'bake', 'mix', 'pour', 'catch',
        'throw', 'kick', 'lift', 'push', 'pull', 'carry', 'drop', 'hold',
        'open', 'close', 'lock', 'unlock', 'tie', 'untie', 'fold', 'unfold',
        'pack', 'unpack', 'wrap', 'unwrap', 'light', 'darken', 'shine', 'glow',
        'spark', 'burst', 'crash', 'smash', 'break', 'fix', 'mend', 'heal',
        'save', 'guard', 'watch', 'wait', 'listen', 'shout', 'whisper', 'call',
        'answer', 'ask', 'tell', 'show', 'hide', 'lead', 'follow', 'join',
        'leave', 'enter', 'exit', 'begin', 'end', 'finish', 'start', 'stop',
        'pause', 'resume', 'win', 'lose', 'share', 'trade', 'send', 'receive'
    ];
    
    // Randomly choose between 2 or 3 words
    $wordCount = rand(2, 3);
    
    if ($wordCount === 2) {
        // Pair: adjective + noun
        $word1 = $adjectives[array_rand($adjectives)];
        $word2 = $nouns[array_rand($nouns)];
        return $word1 . '-' . $word2;
    } else {
        // Triplet: adjective + noun + verb
        $word1 = $adjectives[array_rand($adjectives)];
        $word2 = $nouns[array_rand($nouns)];
        $word3 = $verbs[array_rand($verbs)];
        return $word1 . '-' . $word2 . '-' . $word3;
    }
}

// Function to check if room name already exists
function roomExists($roomName) {
    $usedRoomsFile = 'used_rooms.txt';
    if (!file_exists($usedRoomsFile)) {
        return false;
    }
    
    $usedRooms = file($usedRoomsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return in_array($roomName, $usedRooms);
}

// Function to save room name to used rooms list
function saveRoomName($roomName) {
    $usedRoomsFile = 'used_rooms.txt';
    file_put_contents($usedRoomsFile, $roomName . "\n", FILE_APPEND | LOCK_EX);
}

// Generate a unique room name
$maxAttempts = 100; // Prevent infinite loop
$roomName = '';
$attempts = 0;

do {
    $roomName = generateRoomName();
    $attempts++;
} while (roomExists($roomName) && $attempts < $maxAttempts);

// If we couldn't generate a unique name after max attempts, add timestamp
if ($attempts >= $maxAttempts) {
    $roomName = generateRoomName() . '-' . time();
}

// Save the room name
saveRoomName($roomName);

// Redirect to the new room
header('Location: /' . $roomName);
exit;
?>
