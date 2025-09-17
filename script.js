var answer = 0;
var pc=null
var localStream=null;
var ws=null;
var isInitiator = false;
var isConnected = false;
var clientId = null;
var waitingForAnswer = false;

// Generate unique client ID
var unique = Math.floor(100000 + Math.random() * 900000).toString();

var localVideo = document.getElementById('localVideo');
var remoteVideo = document.getElementById('remoteVideo');
var clientIdElement = document.getElementById('clientId');
var roomIdElement = document.getElementById('roomId');
var connectionStatusElement = document.getElementById('connectionStatus');
var videoContainer = document.getElementById('videoContainer');
var controls = document.getElementById('controls');
var mouseArea = document.getElementById('mouseArea');
var mobileControlToggle = document.getElementById('mobileControlToggle');
var bandwidthSlider = document.getElementById('bandwidthSlider');
var bandwidthTooltip = document.getElementById('bandwidthTooltip');
var configuration  = {
    // 'iceServers': [
	// 	{ 'urls': 'stun:stun.stunprotocol.org:3478' },
	// 	{ 'urls': 'stun:stun.l.google.com:19302' }
    // ]
};

// Function to update video layout based on connection state
function updateVideoLayout() {
    if (isConnected) {
        videoContainer.className = 'video-container connected';
    } else {
        videoContainer.className = 'video-container pre-connection';
    }
}

// Function to show controls
function showControls() {
    controls.classList.add('show');
}

// Function to hide controls
function hideControls() {
    controls.classList.remove('show');
    mobileControlToggle.classList.remove('active');
}

// Function to toggle controls (for mobile)
function toggleControls() {
    if (controls.classList.contains('show')) {
        hideControls();
    } else {
        showControls();
        mobileControlToggle.classList.add('active');
    }
}

// Mouse event handlers for controls visibility
mouseArea.addEventListener('mouseenter', showControls);
mouseArea.addEventListener('mouseleave', hideControls);
controls.addEventListener('mouseenter', showControls);
controls.addEventListener('mouseleave', hideControls);

// Mobile toggle event handler
mobileControlToggle.addEventListener('click', toggleControls);

// Touch event handlers for mobile (to prevent conflicts with video controls)
mobileControlToggle.addEventListener('touchstart', function(e) {
    e.preventDefault();
    e.stopPropagation();
});

mobileControlToggle.addEventListener('touchend', function(e) {
    e.preventDefault();
    e.stopPropagation();
    toggleControls();
});

// Bandwidth slider tooltip functionality
function updateBandwidthTooltip() {
    var value = parseInt(bandwidthSlider.value);
    var bandwidthValues = ['100 kbps', '500 kbps', '1 Mbps', '5 Mbps', '15 Mbps', '40 Mbps'];
    bandwidthTooltip.textContent = bandwidthValues[value];
    bandwidthTooltip.classList.add('show');
}

// Hide tooltip when not interacting with slider
bandwidthSlider.addEventListener('mouseleave', function() {
    bandwidthTooltip.classList.remove('show');
});

bandwidthSlider.addEventListener('mouseenter', function() {
    bandwidthTooltip.classList.add('show');
});

// Video settings based on bandwidth levels
var videoSettings = [
    // Low (100 kbps)
    {
        width: { ideal: 320, max: 480 },
        height: { ideal: 240, max: 360 },
        frameRate: { ideal: 15, max: 20 },
        facingMode: "user"
    },
    // Medium (500 kbps)
    {
        width: { ideal: 640, max: 640 },
        height: { ideal: 480, max: 480 },
        frameRate: { ideal: 20, max: 25 },
        facingMode: "user"
    },
    // High (1 Mbps)
    {
        width: { ideal: 640, max: 1280 },
        height: { ideal: 480, max: 720 },
        frameRate: { ideal: 25, max: 30 },
        facingMode: "user"
    },
    // Ultra (5 Mbps)
    {
        width: { ideal: 1280, max: 1280 },
        height: { ideal: 720, max: 720 },
        frameRate: { ideal: 30, max: 30 },
        facingMode: "user"
    },
    // Premium (15 Mbps)
    {
        width: { ideal: 1920, max: 1920 },
        height: { ideal: 1080, max: 1080 },
        frameRate: { ideal: 60, max: 60 },
        facingMode: "user"
    },
    // Crazy (40 Mbps)
    {
        width: { ideal: 1920, max: 1920 },
        height: { ideal: 1080, max: 1080 },
        frameRate: { ideal: 60, max: 60 },
        facingMode: "user"
    }
];

// Function to get current video settings based on slider
function getCurrentVideoSettings() {
    var sliderValue = parseInt(bandwidthSlider.value);
    return videoSettings[sliderValue];
}

// Function to restart video with new settings
function restartVideoWithNewSettings() {
    var videoSettings = getCurrentVideoSettings();
    var sliderValue = parseInt(bandwidthSlider.value);
    var bandwidthNames = ['Low (100k)', 'Medium (500k)', 'High (1M)', 'Ultra (5M)', 'Premium (15M)', 'Crazy (40M)'];
    
    console.log(`Changing video settings to ${bandwidthNames[sliderValue]}:`, videoSettings);
    
    navigator.mediaDevices.getUserMedia({
        audio: true,
        video: videoSettings
    }).then(function (newStream) {
        // If we have a peer connection, replace tracks instead of removing/adding
        if (pc && pc.getSenders) {
            const senders = pc.getSenders();
            const newTracks = newStream.getTracks();
            
            // Replace existing tracks with new ones
            newTracks.forEach(newTrack => {
                const sender = senders.find(s => s.track && s.track.kind === newTrack.kind);
                if (sender) {
                    // Replace the existing track
                    sender.replaceTrack(newTrack).then(() => {
                        console.log(`Successfully replaced ${newTrack.kind} track`);
                    }).catch(e => {
                        console.log(`Error replacing ${newTrack.kind} track:`, e);
                    });
                } else {
                    // If no existing sender for this track type, add it
                    pc.addTrack(newTrack, newStream);
                }
            });
        }
        
        // Stop old tracks after successful replacement
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
        }
        
        // Update local video and stream reference
        localVideo.srcObject = newStream;
        localStream = newStream;
        
        // Ensure remote video is still playing (safety check)
        if (remoteVideo.srcObject && remoteVideo.paused) {
            remoteVideo.play().catch(e => {
                console.log('Error resuming remote video:', e);
            });
        }
        
        console.log("Video successfully restarted with new settings");
    }).catch(function (e) {
        console.log("Problem while restarting video with new settings:", e);
    });
}

// Start
navigator.mediaDevices.getUserMedia({
        audio: true,
        video: getCurrentVideoSettings()
    }).then(function (stream) {
        localVideo.srcObject = stream;
        localStream = stream;

        // Initialize layout
        updateVideoLayout();
        
        // Set room ID in interface
        roomIdElement.textContent = roomId;

        try {
            ws = new EventSource('serverGet.php?unique='+unique+'&room='+encodeURIComponent(roomId));
        } catch(e) {
            console.error("Could not create eventSource ",e);
        }

        // Websocket-hack: EventSource does not have a 'send()'
        // so I use an ajax-xmlHttpRequest for posting data.
        // Now the eventsource-functions are equal to websocket.
		ws.send = function send(message) {
			 var xhttp = new XMLHttpRequest();
			 xhttp.onreadystatechange = function() {
				 if (this.readyState!=4) {
				   return;
				 }
				 if (this.status != 200) {
				   console.log("Error sending to server with message: " +message);
				 }
			 };
				 xhttp.open('POST', 'serverPost.php?unique='+unique+'&room='+encodeURIComponent(roomId), true);
			 xhttp.setRequestHeader("Content-Type","Application/X-Www-Form-Urlencoded");
			 xhttp.send(message);
		}

        // Websocket-hack: onmessage is extended for receiving 
        // multiple events at once for speed, because the polling 
        // frequency of EventSource is low.
		ws.onmessage = function(e) {
			if (e.data.includes("_MULTIPLEVENTS_")) {
				multiple = e.data.split("_MULTIPLEVENTS_");
				for (x=0; x<multiple.length; x++) {
					onsinglemessage(multiple[x]);
				}
			} else {
				onsinglemessage(e.data);
			}
		}

        // Go show myself
        localVideo.addEventListener('loadedmetadata', 
            function () {
                if (!isConnected) {
                    console.log("Client ID: " + unique);
                    clientIdElement.textContent = unique;
                    connectionStatusElement.textContent = "Connecting...";
                    publish('client-call', {clientId: unique});
                }
            }
        );
		
    }).catch(function (e) {
        console.log("Problem while getting audio/video stuff ",e);
    });
	

function onsinglemessage(data) {
    // Clean the data to remove any non-JSON characters
    data = data.trim();
    
    // Find the first complete JSON object
    var jsonStart = data.indexOf('{');
    var jsonEnd = data.lastIndexOf('}');
    
    if (jsonStart !== -1 && jsonEnd !== -1 && jsonEnd > jsonStart) {
        data = data.substring(jsonStart, jsonEnd + 1);
    }
    
    try {
        var package = JSON.parse(data);
        var data = package.data;
    } catch (e) {
        console.error("Failed to parse JSON data:", e);
        console.error("Raw data:", data);
        return;
    }
    
    console.log("received single message: " + package.event + " from client: " + (data && data.clientId ? data.clientId : 'unknown'));
    
    // Ignore messages from ourselves
    if (data && data.clientId && data.clientId === unique) {
        console.log("Ignoring message from self");
        return;
    }
    
    switch (package.event) {
        case 'client-call':
            // Only respond to client-call if we haven't already initiated and it's from another client
            if (!isInitiator && !isConnected && data && data.clientId !== unique) {
                console.log("Responding to client-call from: " + data.clientId);
                isInitiator = true;
                clientId = data.clientId;
                icecandidate(localStream);
                pc.createOffer({
                    offerToReceiveAudio: 1,
                    offerToReceiveVideo: 1
                }).then(function (desc) {
                    pc.setLocalDescription(desc).then(
                        function () {
                            publish('client-offer', {clientId: unique, offer: pc.localDescription});
                            waitingForAnswer = true;
                        }
                    ).catch(function (e) {
                        console.log("Problem with publishing client offer"+e);
                    });
                }).catch(function (e) {
                    console.log("Problem while doing client-call: "+e);
                });
            }
            break;
        case 'client-answer':
            if (pc==null) {
                console.error('Before processing the client-answer, I need a client-offer');
                break;
            }
            if (data && data.clientId === clientId && waitingForAnswer) {
                console.log("Processing client-answer from: " + data.clientId);
                pc.setRemoteDescription(new RTCSessionDescription(data.answer)).then(function(){
                    console.log("Remote description set successfully");
                    waitingForAnswer = false;
                }).catch(function(e) { 
                    console.log("Problem while doing client-answer: ",e);
                });
            }
            break;
        case 'client-offer':
            if (!isInitiator && !isConnected && data && data.clientId !== unique) {
                console.log("Responding to client-offer from: " + data.clientId);
                isInitiator = true;
                clientId = data.clientId;
                icecandidate(localStream);
                pc.setRemoteDescription(new RTCSessionDescription(data.offer)).then(function(){
                    if (!answer) {
                        console.log("Creating answer...");
                        pc.createAnswer().then(function (desc) {
                            pc.setLocalDescription(desc).then(function () {
                                console.log("Sending answer back to: " + data.clientId);
                                publish('client-answer', {clientId: unique, answer: pc.localDescription});
                            }).catch(function(e){
                                console.log("Problem getting client answer: ",e);
                            });
                        }).catch(function(e){
                            console.log("Problem while doing client-offer: ",e);
                        });
                        answer = 1;
                    }
                }).catch(function(e){
                    console.log("Problem while doing client-offer2: ",e);
                });
            }
            break;
        case 'client-candidate':
           if (pc==null) {
                console.error('Before processing the client-candidate, I need a peer connection');
                break;
            }
            if (data && data.clientId === clientId) {
                console.log("Adding ICE candidate from: " + data.clientId);
                pc.addIceCandidate(new RTCIceCandidate(data.candidate)).then(function(){
                    console.log("ICE candidate added successfully");
                }).catch(function(e) { 
                    console.log("Problem adding ice candidate: "+e);
                });
            }
            break;
    }
};

function icecandidate(localStream) {
    pc = new RTCPeerConnection(configuration);
    pc.onicecandidate = function (event) {
        if (event.candidate) {
            publish('client-candidate', {clientId: unique, candidate: event.candidate});
        }
    };
    try {
        pc.addStream(localStream);
    }catch(e){
        var tracks = localStream.getTracks();
        for(var i=0;i<tracks.length;i++){
            pc.addTrack(tracks[i], localStream);
        }
    }
    
    // Store reference to current stream for potential updates
    pc.currentStream = localStream;
    
    // Set bandwidth limits after connection is established
    pc.ontrack = function (e) {
        console.log("Received remote stream");
        console.log("Remote stream tracks:", e.streams[0].getTracks());
        console.log("Remote stream active:", e.streams[0].active);
        
        remoteVideo.srcObject = e.streams[0];
        
        // Check if video element has the stream
        console.log("Remote video srcObject:", remoteVideo.srcObject);
        console.log("Remote video readyState:", remoteVideo.readyState);
        
        // Force video to play
        remoteVideo.play().then(() => {
            console.log("Remote video started playing");
        }).catch(e => {
            console.log("Error playing remote video:", e);
        });
        
        isConnected = true;
        connectionStatusElement.textContent = "Connected!";
        
        // Update video layout when connected
        updateVideoLayout();
        
        // Apply bandwidth limits after connection
        setTimeout(applyBandwidthLimits, 1000);
    };
    
    pc.onconnectionstatechange = function() {
        console.log("Connection state: " + pc.connectionState);
        connectionStatusElement.textContent = pc.connectionState;
        if (pc.connectionState === 'connected') {
            isConnected = true;
            updateVideoLayout();
        } else if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed') {
            isConnected = false;
            updateVideoLayout();
        }
    };
    
    pc.oniceconnectionstatechange = function() {
        console.log("ICE connection state: " + pc.iceConnectionState);
    };
    
    pc.onsignalingstatechange = function() {
        console.log("Signaling state: " + pc.signalingState);
    };
}

function publish(event, data) {
    console.log("sending ws.send: " + event);
    ws.send(JSON.stringify({
        event:event,
        data:data
    }));
}

// Bandwidth control functions
function changeBandwidth() {
    // Apply bandwidth limits first, then restart video with a small delay
    applyBandwidthLimits();
    
    // Small delay to ensure bandwidth limits are applied before video restart
    setTimeout(() => {
        restartVideoWithNewSettings();
    }, 100);
}

function applyBandwidthLimits() {
    if (!pc || !pc.getSenders) return;
    
    const sliderValue = parseInt(bandwidthSlider.value);
    
    let videoBitrate, audioBitrate;
    
    switch(sliderValue) {
        case 0: // Low
            videoBitrate = 100000;    // 100 kbps
            audioBitrate = 16000;     // 16 kbps
            break;
        case 1: // Medium
            videoBitrate = 500000;    // 500 kbps
            audioBitrate = 32000;     // 32 kbps
            break;
        case 2: // High
            videoBitrate = 1000000;   // 1 Mbps
            audioBitrate = 64000;     // 64 kbps
            break;
        case 3: // Ultra
            videoBitrate = 5000000;   // 5 Mbps
            audioBitrate = 192000;    // 192 kbps
            break;
        case 4: // Premium
            videoBitrate = 15000000;  // 15 Mbps
            audioBitrate = 256000;    // 256 kbps
            break;
        case 5: // Crazy
            videoBitrate = 40000000;  // 40 Mbps
            audioBitrate = 320000;    // 320 kbps
            break;
        default:
            console.log('Invalid bandwidth slider value:', sliderValue);
            return;
    }
    
    console.log(`Applying bandwidth limits: Video=${videoBitrate/1000}kbps, Audio=${audioBitrate/1000}kbps`);
    
    // Apply bandwidth limits to all senders
    const senders = pc.getSenders();
    senders.forEach(sender => {
        if (sender.track && sender.getParameters) {
            sender.getParameters().then(params => {
                if (params.encodings && params.encodings.length > 0) {
                    if (sender.track.kind === 'video') {
                        params.encodings[0].maxBitrate = videoBitrate;
                        console.log('Applied video bitrate limit:', videoBitrate);
                    } else if (sender.track.kind === 'audio') {
                        params.encodings[0].maxBitrate = audioBitrate;
                        console.log('Applied audio bitrate limit:', audioBitrate);
                    }
                    return sender.setParameters(params);
                }
            }).catch(e => {
                console.log('Error getting/setting parameters:', e);
            });
        }
    });
}
