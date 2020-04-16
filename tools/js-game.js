({
    baseUrl: '../www/js'
    , name: 'game'
    , out: '../www/js/game.r.js'
    , shim: {
        'lib/jquery': {
            exports: '$'
        }
        , 'lib/jquery-ui': {
            deps: ['lib/jquery']
            , exports: '$ui'
        }
    }
})
