# LibreOffice

A [docker](https://www.docker.com/) image with
[LibreOffice](https://www.libreoffice.org/) and a very simple API for converting
office files to PDF.

The API is heavily (and almost criminally) inspired by [Collabora Online's
Conversion API](https://sdk.collaboraonline.com/docs/conversion_api.html):

```sh
curl --form data=@my-document.docx http://libreoffice-api:9980/convert-to/pdf --output my-document.pdf
```
