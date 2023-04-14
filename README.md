# Laravel Vue Survey

This is a personal project created to upskill in Vue3 and Laravel. I am following the tutorial below and adapting it slightly to run in docker:

https://www.youtube.com/watch?v=WLQDpY7lOLg&ab_channel=TheCodeholic

Shout out to the Codeaholic!

## Useful Commands

#### Running the project via WSL2 on Windows 11 using Docker & Laravel Sail. 

To run the laravel application from root dir:
    
`./vendor/bin/sail up`

To run Vue from root dir:
   
`cd vue`
    
then:
    
`npm run dev`

#### Ports

Laravel: 80
Vite: 5174
    
#### Database

Database is MySQL, all credentials are located in .env

Migration: 
    `vendor/bin/sail artisan migrate`
