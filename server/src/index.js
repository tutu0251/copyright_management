require('dotenv/config');
const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const morgan = require('morgan');
const path = require('path');
const { connectDb } = require('./config/db.js');
const apiRoutes = require('./routes/index.js');

const app = express();
const port = Number(process.env.PORT) || 5000;
const clientUrl = process.env.CLIENT_URL || 'http://localhost:5173';

app.use(morgan('dev'));
app.use(cors({ origin: clientUrl, credentials: true }));
// express.json() did not exist until Express 4.16; on 4.15.x use body-parser.
app.use(bodyParser.json());

app.use('/api', apiRoutes);

const clientDist = path.join(__dirname, '../../client/dist');
app.use(express.static(clientDist));
app.get('*', (req, res, next) => {
  if (req.path.startsWith('/api')) return next();
  res.sendFile(path.join(clientDist, 'index.html'), (err) => {
    if (err) next();
  });
});

async function start() {
  await connectDb();
  app.listen(port, () => {
    console.log(`API listening on http://localhost:${port}`);
  });
}

start();
