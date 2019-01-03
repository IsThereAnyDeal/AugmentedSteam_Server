# Enhanced Steam API Server

This is the PHP server application that powers the Enhanced Steam API.  You can download this repository, configure it using your own external API keys and endpoints, and serve clients running [Enhanced Steam](http://https://github.com/jshackles/Enhanced_Steam "Enhanced Steam").

Please be aware that this code has been running on api.enhancedsteam.com for several years, and it was never my intention to release this publicly.  As such, please understand that the source files contain code spaghetti, bad practices, outdated methods, weird loops etc but should be 100% functional.  Pull requests will only be accepted for code cleanup and refactoring, or from official sources which control external data endpoints.

### Features
-------

- A fully functional replica of api.enhancedsteam.com including all API endpoints
- Convenient configuration file to configure external data sources
- Global error handling function
- Included MySQL database structure that can be easily imported
- Comments are hidden throughout so that you occassionally dont have to make guesses about what something does

### Installation 
-------

1. Download or clone this repository, place the files on the root of your API server running PHP 5.6.
2. Run the included enhancedsteam.sql on your MySQL database to create the expected data structure.
3. Edit config.php with your database information, API keys, and API endpoint URIs to enable various external services.
4. Customize as you see fit.
5. Point your Enhanced Steam client to your new API server.

### External APIs
-------

The included config.php file requires the following API keys be obtained for full compatibility:
- Steam API Key (for proxying Steam API requests)
- Open Exchange Rates API Key (for currency conversion data)
- OpenCritic API Key (to retrieve review data from OpenCritic)
- IsThereAnyDeal.com API Key (to retrieve pricing information)
- Twitch API Key (to get streamer data on Steam profiles)

Additionally, the following endpoints will need to be entered for full compatibility:
- WSGF (for widescreen information)
- SteamSpy (for sales data on store pages)
- Steam.Tools (for retrieving and caching market data)
- SteamRep (for determining user reputation status)
- PC Gaming Wiki (to get info on game fixes)

*These API keys and Endpoint URIs are your responsibility to obtain from their respective sites.*

### Conventions
-------

By default, all data output from this server will be in JSON format.  This behavior can be changed by altering the config.php file.

The Access-Control-Allow-Origin is set to \* to maximize compatibility for testing, but it is recommended that this statement be removed and/or replaced before running this software in a production environment.

All inputs received from users are wrapped in mysql_real_escape_string either when a $\_GET value is stored as a variable, or when that variable is used in an SQL statement, or both.  Additional sanity checks, such as checking if the provided input is a number, is done where possible.

Data obtained from external endpoints is sometimes cached to the local MySQL server for later quick retrieval.  This was done to reduce the load on external sites against millions of Enhanced Steam API users hitting their endpoints daily, please keep these conventions in place to extend the same courtesies.

Some API functions (such as EXFGLS, SUPPORTER, and HLTB) rely on existing data stored in the MySQL database.  Collecting and storing this data is outside of the scope of this repository in most cases.

Files labeled cron.php are designed to be run using a task scheduler such as cron.  These files are typically used to pull the newest relevant data (such as exchange rate data, Steam marketplace data, etc) from external sources so that it may be cached locally.  Please check with the external data providers Terms of Service regarding how frequently you are able to use these endpoint URIs and set your cron schedule accordingly.

### Support
-------

This open source implimentation of the Enhanced Steam API server is not being actively supported.  The official API server (api.enhancedsteam.com) will cease to function on January 31st, 2019.  Due to the nature of the internet, many of the included applications could cease to function at any time as external data providers may change without notice.  This software is being provided as-is, where-is, without support from its original developer.

### License
-------

Enhanced Steam API Server is Copyright 2012-2019 Jason Shackles.  This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License v3 or newer as published by the Free Software Foundation.  A copy of the GNU General Public License v3 can be found in [LICENSE](LICENSE) or at https://www.gnu.org/licenses/gpl-3.0.html.