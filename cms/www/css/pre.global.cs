/*
 * Coded by Steven Bower (cc) 2011
 * TurnWheel Designs
 * http://turnwheel.com
 */

/* Google Web Fonts */
/* @import "http://fonts.googleapis.com/css?family=Marck+Script|Varela+Round"; */

/* ColorBox */
@import "/css/jquery.colorbox.css";

/* Global Defintions */
* {
	margin: 0;
	padding: 0;
}
html, body {
	height: 100%;
}
body {
	background: transparent url(/images/bg.jpg) repeat;
	color: #3c3c3c;
	margin: 0;
	padding: 0;
	letter-spacing: .5px;
	line-height: 1.5em;
	font-weight: normal;
	font-size: 14px;
	font-family: 'Arial', sans-serif;
}
a {
	color: #006FAE;
	text-decoration: underline;
}
a:hover {
	text-decoration: none;
}
img, a img {
	border: 0;
	outline: 0;
}
h1 {
	display: none;
}
h2 {
	font-family: cursive;
	font-size: 2em;
	color: #2A0E6B;
	margin: 15px 0 15px 0;
}
h2 a {
	text-decoration: none;
	color: #2A0E6B;
}
h3 {
	font-family: cursive;
	font-size: 1em;
	color: #464646;
	margin: 15px 0 15px 0;
}
h4 {
	font-family: cursive;
	font-size: 1em;
	color: #464646;
	margin: 15px 0 15px 0;
}
p {
	padding-top: 5px;
	padding-bottom: 5px;
}
hr {
	margin: 30px 0 25px 0;
	color: #D9D9D9;
}
input {
	background: #FFF;
	color: #000;
	border: 1px solid #482670;
	outline: 0;
	height: 25px;
	min-width: 200px;
}
textarea {
	background: #FFF;
	color: #000;
	border: 1px solid #482670;
	outline: 0;
}
input[type="checkbox"], input[type="radio"] {
	height: auto;
	width: auto;
	min-width: 0;
}
button {
	text-transform: uppercase;
	text-align: center;
	background: #482670;
	color: #E9E9E9;
	padding: 5px;
	border: 1px solid #482670;
}
button.link span {
	font-family: Arial, sans-serif;
	font-size: 18px;
	padding: 5px;
}
button:hover {
	background: #7E4EB7;
	border: 1px solid #D09EFF;
}
label {
	cursor: pointer;
}
fieldset {
	margin-left: 10px;
	margin-bottom: 25px;
	padding-top: 5px;
	padding-left: 15px;
	border: 1px solid #44226C;
	background-color: #D5A8E1;
	color: #000;
}
legend {
	font-size: 1em;
	font-family: sans-serif;
	letter-spacing: 1.5px;
	background: #805292;
	padding: 3px;
	margin: 5px 0px 8px 0px;
	border: 1px solid #D6AEE3;
	color: #FFF;
	font-weight: 700;
}
table {
	width: 80%;
}
tr.table0 {
	background-color: #e3c7ec;
}
tr.table1 {
	background-color: transparent;
}
td {
	min-width: 150px;
	padding: 3px;
	vertical-align: top;
}

/* Template Definitions */
#top {
	width: 100%;
	height: 170px;
	background: transparent url(/images/topbg.jpg) top left repeat-x;
}
#container {
	margin: 0 auto;
	position: relative;
	min-height: 100%;
	height: auto !important;
	width: 994px;
	background: transparent;
}
#header {
	position: absolute;
	margin: 0 auto;
	top: -170px;
	width: 975px;
	background: transparent;
}
#logo {
	float: left;
	width: 400px;
	background: transparent url(/images/logo2.png) no-repeat;
	background-position: 10px 15px;
}
#logo a {
	display: block;
	width: 400px;
	height: 100px;
}
#logo b {
	display: none;
}
#contactinfo {
	padding-top: 15px;
	float: right;
	color: #FFF;
	font-size: 1.2em;
	text-align: right;
}
#contactinfo .phone {
	display: block;
	font-size: 20px;
	margin-bottom: 10px;
}
#contactinfo .address {
	display: block;
	font-size: 14px;
}
#menu {
	margin: 0px;
	height: 40px;
	position: absolute;
	top: 125px;
	left: 66px;
}
#menu li {
	list-style: none;
	position: absolute;
	top: 0;
}
#menu a {
	font-family: 'Century Gothic', sans-serif;
	font-size: 14px;
	text-transform: uppercase;
	text-decoration: none;
	padding-top: 8px;
	color: #FFF;
	height: 30px;
	display: block;
	background: transparent;
	outline: 0;
	text-shadow: 1px 1px 0px #777;
}
#menu a:hover {
	color: #FFF;
	text-shadow:0px 0px 4px #FFF;
}
#content {
	width: 970px;
	padding: 40px 0 50px 10px;
}
#content ul li {
	margin: 10px 0 20px 15px;
}
#content_inner {
	float: left;
	width: 600px;
}
#content_side {
	float: right;
	width: 300px;
	margin: 0;
	padding: 0 10px 10px 10px;
	background: #F5DEF0;
}
#content_side h2 {
	font-family: 'Varela Round', cursive;
	font-size: 1.125em;
	color: #2A0E6B;
	margin: 10px 0 10px 0;
}
#footer_top {
	width: 100%;
	height: 7px;
	background: transparent url(/images/footer_top_bg.jpg) repeat-x top center;
	overflow: hidden;
}
#footer {
	width: 100%;
	height: 160px;
	background: transparent url(/images/footerbg.jpg) repeat;
}
#footer_inner {
	margin: 0 auto;
	height: 150px;
	width: 996px;
	font-family: 'Arial', sans-serif;
	color: #006FAE;
	padding-top: 10px;
	background: transparent url(/images/footerbg.jpg) repeat;
}
#footer_inner a {
	font-family: 'Arial', sans-serif;
	color: #006FAE;
	text-decoration: none;
}
#footer_inner a:hover {
	text-decoration: underline;
}
#footer_inner p {
	margin: 5px 0;
	padding: 0;
	text-align: center;
}
#footer_inner .copyright {
	font-size: 0.8em;
	color: #636363;
}
#footer_inner .copyright a {
	color: #636363;
	text-decoration: underline;
}
#footer_inner .copyright a:hover {
	text-decoration: none;
}

/* Social Media Links */
.social {
	margin: 0 auto;
	width: 160px;
	padding: 15px 0 10px 5px;
	height: 44px;
	background: transparent;
}

.social a {
	display: block;
	width: 45px;
	height: 44px;
	float: left;
	margin: 0 3px 0 3px;
	background: transparent url(/images/social.png) no-repeat;
}
.social b {
	display: none;
}
.social .facebook {
	background-position: 0 0;
}
.social .yelp {
	background-position: -55px 0;
}
.social .google {
	background-position: -110px 0;
}

/* Misc. Classes */
.border {
	background-color: #948FC5;
	padding: 5px;
	text-align: center;
}
.border img {
	margin: 0 auto;
}
.error, .red {
	color: #FF0000;
}
.hidden {
	display: none;
}
.clear {
	clear: both;
	background: none;
}
.center {
	margin: 0 auto;
	text-align: center;
}
.bold {
	font-weight: bold;
}
.quote {
	text-align: justify;
	font-style: italic;
}
.quote_author {
	text-align: right;
}
.right {
	text-align: right;
}
.left {
	text-align: left;
}
.fright {
	float: right;
}
.fleft {
	float: left;
}
.box {
	border: 1px solid #44226C;
	background-color: #D5A8E1;
	color: #000;
	margin-bottom: 10px;
}
.box p {
	padding: 15px;
}
.box strong {
	color: #44226C;
}
.box.error {
	border-color: #FF0000;
}
.box.error strong {
	color: #FF0000;
}
