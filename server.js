const express = require('express');

const app = express();


const server = require('http').createServer(app);


const io = require('socket.io')(server, {
    cors: { origin: "*"}
    // cors: {
    //     origin: "http://localhost:3001",
    //     methods: ["GET", "POST"],
    //     allowedHeaders: ['*'],
    //     credentials: true
    // }
});


io.on('connection', (socket) => {
    console.log('connection');

    socket.on('sendChatToServer', (message) => {
        console.log(message);
        socket.broadcast.emit('sendChatToClient', message);
    });

    socket.on('sendNotificationToServer', (message) => {
        console.log(message);
        socket.broadcast.emit('sendNotificationToClient', message);
    });


    socket.on('disconnect', (socket) => {
        console.log('Disconnect');
    });
});

server.listen(3001, () => {
    console.log('Server is running');
});