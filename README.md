# Report-UI

The purpose of the script is to unify the authentication system of the php based reports app (https://github.com/shalewagner/git_iSante) with openmrs. 


## Installing

* Make a backup of your current reports backend.php, backendAddon.php, banner.php, bannerbody.php, index.php, splash.php files (so you can easily reverse this if anything get broken :)). All files are located in the root directory of the reports app
* Copy the entire content of this repo into your root directory. This should replace the files listed above as well as create an "openmrs" directory
* cd into "openmrs" and make "tmp" and "isante_bridge.json" writable
* Open "isante_bridge.json" in a text editor and change value of "url" to that of your openmrs installation and save.  Example below: https://52.37.13.123:8080 should be your openmrs setup

```
{
  "isante":{
    "url":"https://52.37.13.123:8080"
  }
}
```
* Now your reports website should redirect to the openmrs installation when you try to access it

* Install and configure the reports bridging webapp by following the instructions here - https://github.com/IsantePlus/reports-webapp. 

### Building your own connection from openmrs

The reports webapp (https://github.com/IsantePlus/reports-webapp) will only allow users access the reports from the openmrs admin. The following instruction will guide you in creating a link to your Reports installation, from an openmrs module for instance, while maintaining the session:

* In your app make an api call to the "{openmrs}/ws/rest/v1/session" end point. The response should be a json object containing the authenticated users data.
* Pass the data in json format as the value of the "isante_session" parameter within your link to the reports website. See sample below:

```
  http://myopenmrsreports.com/?isante_session={authenticated users json data}
```
