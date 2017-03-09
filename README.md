COMMUNECTED : get connected to your city
===========
COMMUNECTED is part of the citizen's toolbox developped by the open-source network : HUMAN PIXEL.
With simply an email and his postal code, a citizen get connected to his city and all its actors.
Through a dynamic interface each citizen can :
- get linked to any fellow citizen sharing his interests,
- interact with locals organisations (associations or entreprises)
- and communicate directly with his town hall.
Easily connected with their proximity network, citizens build local value from the usefulness of their interaction.
More  aware of neighbourhood intiatives and stake, citizens can get more involved in district council decisions and local actions.

ASAP
===========
we need asap the a json-ld smaple descriptions of : 
- [person](https://github.com/pixelhumain/buildingCommons/blob/master/person.json)
- [projects]()
- [organizations (NGO, Company, Informel Group) ]()
- [discussion]()
- [event]() 
- [city]()


current Status 
===========
released : xx
url : 
implementing : Version0.1


Roadmap & Milestones
===========
	
** 0.13 BREATH DEAPLY >> not started >> 31/04/2016
- sous evnnement 
- ouverture international 
- multi scope

** 0.12 BREATH STRONG > 13/04/2016
- amélioration du systeme validation 
- refactor et ajout des images sur les news
- réintégration du module de vite et de discussion
- module network , communecter en marque blanche 
- refactor de la structure des cities et des codepostaux
- intégration de divers de data , makery, bretagne Telecom, commun59, service public
- système et admin de modération
- refonte et design de mail 
- gamification avec les actions

** 0.1
stabalising all current features 
for an alpha , private release

- Entry point 
	- The platform's content information is fully open 
	- loging in give access to 
		- notifications
		- creating an Organization
		- participating
		- and much more
- Dashboard
	- Organization of type Association (NGOs)
		- List of associations
		- add an association
	- Organization of type Company
		- List of companies
		- Add a company 
	- Périscolaire
	- Information
- Person
	- can register 
	- can login
	- searched by tag
- Organizations
	- can be created by a registered person
		- can contain people 
		- can contain organisations 
	- be joined by a person
	- searched by tag
	- can be public or private
- Events
	- can be created by a person or an Organization
	- can be viewed in a calendar
	- searchable by location, by tags
	- show event lsit by scope 
	- visualisations
		- list
		- calendar
- City 
	- exists when simply one person is in a postalCode
	- contains People, Organizations
	- has Events
- OpenData
	- re write all person, Organization entities into conform JSON-LD
	- Generic API for all elements
	- translation system into any given ontology ( Schema, ActivityStream, Portable Linked Profile )
- local search Organization
- Search Engine
	- search by scope : city scope or region scope
- Tagging Module
	- add tags on person & Organization
	- search by tags

Think Tank & wishlist
===========
Proxicity : a value calculating the capacity of a territory in creating links based on a social plateforms activity

Partners
===========
We use [BrowserStack](https://www.browserstack.com/) to test our platfom on different OS and browser. Amazing tool.
We are patner and the support our open source project. Thank you to them.

Help keep this project alive
===========
contribute or Join the NGO on [Hello Asso](https://www.helloasso.com/associations/open-atlas/adhesions/soutenez-et-adherez-a-open-atlas)

Contribute to the wishlist 
===========
[![Feature Requests](http://feathub.com/pixelhumain/communecter?format=svg)](http://feathub.com/pixelhumain/communecter)
=======
CTK : Citizen ToolKit 
===
	The modular (H)API driven architecture also called CTK : Citizen ToolKit 
	allows developpers to build efficiently citzen oriented application 
	starting with function point definitions, then building the corresponding API 
	and finally building the Front end on any device. 

	The project grows in time, by building all sorts of citizen oriented applications 
	based on a common code base : CTK 

	it is intended to be generic , semanticly standardised by ontologies of schema.org 
	linked by referenced data 

Api architecture 
===
	toutes les definitions de block mutualisés sont listés dans la classe Api.php
	dans une map unique : Api::$apis
	on peut faire des bouquets de fonction ou regroupements grace a des constructeur comme Api::getUserMap()
	chaque application qui a son ApiController peut alors utiliser une map generique, l'augmenter, ou en creer une specifique 
	les actions ou controller sont chargé dynamiquement basé sur ces definitions

Application Developpement Best practise
===
	when building apps in test mode use application specific information like email:myapp@myapp.com
	this it'll be easy to extract 
	if your data is part of a common table simple add a applications.myapp node 
	this node can contain any application specific information

Admin users 
===
	The api interface is retricted just like a back office for admin credentials
	there are 2 types of admin credentials 
		application level admin 
			these users carry a applications.myapp.isAdmin:true node on their account's json
			are considered as admin user only for the applications called myapp
		full phAdmin 
			these users carry an isAdmin:true node on their account's json
			they have access to all applications 

Application specifications
===
	creating a new applications 
		there must be an entry in the applications collections
		it will carry all details and specifications for the app to run 
		the system will and can get attributes from this entry 
		declare the module in the protected/config/moduleconfig.php 

InitData process and architecture
===
	any application might need bootstrap datasets to be able to run properly 
	simply create a /data folder 
	inside add all your datasets like this 
		filenames must be the destination collections name : ex : citoyens.json
		inside they must contain an array list of json entries 
	in the applications api you'll find an adminPH section listing all the files available for initialisation 
	click "initialize data" 

