let config = require('../config'),
    express = require('express'),
    cors = require('cors'),
    bodyParser = require('body-parser'),
    app = express(),
    fs = require("fs");

//config.mode was going to be used to swith between using the json file or pulling from the mysql db
//needed for getting post data
app.use(bodyParser.json());

app.use(bodyParser.urlencoded({extended: true}));

//needed for local testing
app.use(cors());

let users = {};

let init = () => {

    //we load up the json file if we are using mode is 'json'
    if (config.mode === 'json') loadUsers();

};

//loads data from the json file

let loadUsers = () => {
  fs.readFile( __dirname + "/../data/" + "students_classes.json", 'utf8', (err, data) => {
    users  = JSON.parse( data );
  });

};

//this was going to be used to initialize the data in the db
app.get('/init', (req, res) => {

});

app.post('/api/findStudents', (req, res) => {

  let found = findUsers(req.body.params.fname, req.body.params.lname);

  res.end( JSON.stringify(found));

});

app.post('/api/addStudent', (req, res) => {

});

app.get('/', (req, res) => {

  res.end('Welcome to the APi server, nothing to see here :)');

});

app.get('/api/getStudents', (req, res) => {

  res.end(JSON.stringify(users.students));

});

app.get('/api/getClasses', (req, res) => {

  res.end(JSON.stringify(users.classes));

});

/**
 * This method displays the user information based on key in the object
 *
 * @returns json
 */
app.get('/api/:id', (req, res) => {


  let user = users.students[req.params.id];

  res.end( JSON.stringify(user));

});

/**
 * This method was going to used to delete users
 *
 * @returns void
 */
app.delete('/deleteUser', function (req, res) {

});

/**
 * This method finds users and returns them
 *
 * @returns object
 */
findUsers = (fname, lname) => {

  let results = [];

  if (!fname && !lname) return users.students; //return all users

  results = users.students.filter((student) => {

    if(fname && lname){

      return student.first.toLowerCase().indexOf(fname.toLowerCase()) !== -1 && student.last.toLowerCase().indexOf(lname.toLowerCase()) !== -1;
    }
    else if (lname){

      return student.last.toLowerCase().indexOf(lname.toLowerCase()) !== -1;
    }
    else if(fname)

      return student.first.toLowerCase().indexOf(fname.toLowerCase()) !== -1
  });

  return results;
};

/**
 * Startup information
 */
let server = app.listen(config.api_port, () => {
  let host = server.address().address
  let port = server.address().port
  console.log("App listening at http://%s:%s mode:%s", host, port, config.mode);
});

//function that runs when api starts up
init();