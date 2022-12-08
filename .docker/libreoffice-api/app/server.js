'use strict'

const debug = true
const express = require('express')
const unoconv = require('node-unoconv')
const fs = require('fs')

// https://github.com/expressjs/multer#usage
const multer = require('multer')
const upload = multer({ dest: 'tmp/' })

const PORT = 9980
const HOST = '0.0.0.0'

// App
const app = express()

app.get('/', (req, res) => {
  const generateUrl = (path) => `${req.protocol}://${req.get('host')}${path}`

  // List API endpoints.
  res.json({
    'Convert office document to pdf (POST)': generateUrl('/convert-to/pdf')
  })
})

// Handle a single file field, 'data'.
app.post('/convert-to/pdf', upload.single('data'), (req, res, next) => {
  // @see https://github.com/damian66/node-unoconv#-options
  unoconv.convert(req.file.path, {
    debug,
    // https://manpages.ubuntu.com/manpages/trusty/man1/doc2odt.1.html#options
    export: {
      // https://manpages.ubuntu.com/manpages/trusty/man1/doc2odt.1.html#pdf%20export%20filter%20options
      UseTaggedPDF: true
    },
    printer: {
      PaperFormat: 'A4',
      PaperOrientation: 'portrait'
    }
  })
    .then((buffer) => {
      res.send(buffer)
    }).catch((err) => {
      console.error(err)
      next(err)
    })
    .finally(() => {
      fs.stat(req.file.path, (err, stats) => {
        if (err) {
          return console.error(err)
        }

        console.log(`Removing file ${req.file.path}`)
        fs.unlink(req.file.path, (err) => {
          if (err) {
            console.error(err)
          }
        })
      })
    })
})

app.listen(PORT, HOST)

console.log(`Running on http://${HOST}:${PORT}`)
