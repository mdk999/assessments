'use strict';

const request = require('requestretry');
const validurl = require('valid-url');
const config = require('./config');

const sharp = require('sharp');

exports.handler = (event, context, callback) => {

var params = {
		//format: (event.queryStringParameters.f) ? event.queryStringParameters.f.toString() : null,
		quality: (event.queryStringParameters.q) ? parseInt(event.queryStringParameters.q.toString()) : null,
		//height: (event.queryStringParameters.h) ? parseInt(event.queryStringParameters.h.toString()) : null,
		width: (event.queryStringParameters.w) ? parseInt(event.queryStringParameters.w.toString()) : null,
		url: (event.queryStringParameters.u) ? event.queryStringParameters.u.toString() : null
	};

//var format = params.format || 'jpeg';
var quality = params.quality || 75;
//var height = params.height;
var format = 'jpeg';
//var quality = 75;
var height = null;
var width = params.width || 200;
var url = validurl.isUri(params.url) ? params.url : getLongUrl(params.url);

var data = {};

request({uri:url, encoding:null,  maxAttempts: 3,
  retryDelay: 3000,  // (default) wait for 5s before trying again
  retryStrategy: myRetryStrategy}, function (error, response, body) {

      if (!error && response.statusCode == 200) {
  
     sharp(new Buffer(body))
    .resize(width, height)
    .jpeg({quality: quality})
    .toBuffer(function(err,img){
    
    data={
    "isBase64Encoded": true,
    "statusCode": response.statusCode,
    "headers": { "Content-Type": "image/jpeg", Accept: "image/jpeg", "Date": new Date(),"Cache-Control": "public, max-age=604800"},
    "body":img.toString('base64')
    }; 

	callback(null,data);
    });

      }
	else 
	{

	 data={
    "isBase64Encoded": false,
    "statusCode": response.statusCode,
    "headers": { "Content-Type": "application/json", Accept: "application/json", "Cache-Control": "no-cache"},
    "body":error
    };

	callback(null,data);
	
	}

    }); 

function myRetryStrategy(err, response, body){
  // retry the request if we had an error or if the response was a 'Bad Gateway'
  //   return err || response.statusCode === 502;

	var code = response.statusCode.toString();

	var pref = code.slice(0,1);

	return err || pref === '5' || pref == '4';
   }

   function getLongUrl(url){

	var urlArr = url.split('/');	

	if(config.shorturls.hasOwnProperty(urlArr[0])){

	return url.replace(urlArr[0],config.shorturls[urlArr[0]]);

	}

	else return null;	

	}

};
