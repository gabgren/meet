<?php
// Get room parameter
$room = isset($_GET['room']) ? $_GET['room'] : '';

// If no room specified, show blank page
if (empty($room)) {
    exit;
}

// Validate room name
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $room)) {
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
    <title>Invalid Room</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #000;
            color: #fff;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .message {
            text-align: center;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="message">
        <h1>Invalid room name</h1>
        <p>Room names can only contain letters, numbers, hyphens, and underscores</p>
    </div>
</body>
</html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="video-container pre-connection" id="videoContainer">
    <video id="localVideo" autoplay="true" muted="muted"></video>
    <video id="remoteVideo" autoplay="true" playsinline="true"></video>
    
    <div class="mouse-area" id="mouseArea"></div>
    
    <!-- Mobile control toggle dot -->
    <div class="mobile-control-toggle" id="mobileControlToggle">
        <div class="toggle-dot"></div>
    </div>
    
    <div class="controls" id="controls">
        <h3>Meeting Controls</h3>
        <p>Room: <span id="roomId"></span></p>
        <p>Client ID: <span id="clientId"></span></p>
        <p>Connection Status: <span id="connectionStatus">Not connected</span></p>
        <div style="margin-top: 30px;">
            <label>Bandwidth:</label>
            <div class="bandwidth-slider-container">
                <div class="bandwidth-tooltip" id="bandwidthTooltip">15 Mbps</div>
                <input type="range" id="bandwidthSlider" class="bandwidth-slider" 
                       min="0" max="5" value="4" step="1" 
                       oninput="updateBandwidthTooltip(); changeBandwidth()" onchange="changeBandwidth()">
                <div class="bandwidth-labels">
                    <span>100k</span>
                    <span>500k</span>
                    <span>1M</span>
                    <span>5M</span>
                    <span>15M</span>
                    <span>40M</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var roomId = '<?php echo htmlspecialchars($room, ENT_QUOTES, 'UTF-8'); ?>';
</script>
<script src="script.js"></script>
</body>
</html>