import express from 'express';

const app = express();
const port = process.env.PORT || process.env.SERVICE_PORT || 8002;

app.get('/health', (_req, res) => {
  res.json({
    service: 'IoTService',
    status: 'ok',
    timestamp: new Date().toISOString()
  });
});

app.listen(port, () => {
  // eslint-disable-next-line no-console
  console.log(`IoTService placeholder listening on port ${port}`);
});

