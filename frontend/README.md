
# Datahub - Web frontend
Written in Angular.

## Development setup
```shell
npm install    # install dependencies
```

## Run locally
Make sure `apiUrl` in `environments/environment.ts` points to your setup of the backend.

```shell
npm start      # start server at http://localhost:4200
```

## Development cheat sheet
```shell
ng generate component components/InlineEdit    # create new component
ng generate component pages/SensorView         # create new page
ng generate component dialogs/CreateUserDialog # create new dialog
ng generate service services/Authentication    # create new service
ng add @angular/material                       # add Angular Material
ng build                                       # build for production
```

* Material Components: https://material.angular.io/components/
* Icon library: https://fonts.google.com/icons?selected=Material+Icons

## Future improvements
* Mobile friendly
* Store last reading and reading counts for faster read
