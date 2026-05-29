const fs = require('fs');
const https = require('https');
const path = require('path');

const url = 'https://github.com/ultralytics/assets/releases/download/v8.1.0/yolov8n.pt';
const dest = path.join(__dirname, 'ml-service', 'yolov8n.pt');

console.log(`Downloading YOLOv8 weights from ${url}...`);
console.log(`Destination: ${dest}`);

const file = fs.createWriteStream(dest);

https.get(url, function(response) {
    if (response.statusCode !== 200) {
        console.error(`Failed to download: Server returned status code ${response.statusCode}`);
        process.exit(1);
    }
    
    response.pipe(file);
    
    file.on('finish', function() {
        file.close(() => {
            console.log('Download complete successfully! File saved at:', dest);
            process.exit(0);
        });
    });
}).on('error', function(err) {
    fs.unlink(dest, () => {});
    console.error('Error downloading file:', err.message);
    process.exit(1);
});
