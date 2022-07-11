'use strict'
const merge = require('webpack-merge')
const prodEnv = require('./prod.env')

module.exports = merge(prodEnv, {
  NODE_ENV: '"development"',
  URL_ROOT: '"/static"',
  // BASE_API: '"http://localhost/index.php/"',
  BASE_API: '"/api"'
})

