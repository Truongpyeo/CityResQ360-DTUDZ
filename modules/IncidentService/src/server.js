import express from 'express';

const app = express();
const port = process.env.PORT || process.env.SERVICE_PORT || 8001;

app.get('/health', (_req, res) => {
  res.json({
    service: 'IncidentService',
    status: 'ok',
    timestamp: new Date().toISOString()
  });
});

app.listen(port, () => {
  // eslint-disable-next-line no-console
  console.log(`IncidentService placeholder listening on port ${port}`);
});

