let config = require('../config'),
    express = require('express'),
    cors = require('cors'),
    app = express();

app.use(cors());

app.use('/', express.static(__dirname + '/app'));

/**
 * Startup information
 */
let server = app.listen(config.app_port, () => {
  let host = server.address().address
  let port = server.address().port
  console.log("App listening at http://%s:%s mode:%s", host, port, config.mode);
})
