import http from 'http';

const port = process.env.PORT || 8006;

const requestListener = (_req, res) => {
  res.writeHead(200, { 'Content-Type': 'application/json' });
  res.end(
    JSON.stringify({
      status: 'ok',
      service: 'NotificationService',
      timestamp: new Date().toISOString()
    })
  );
};

const server = http.createServer(requestListener);

server.listen(port, () => {
  // eslint-disable-next-line no-console
  console.log(`NotificationService placeholder running on port ${port}`);
});

