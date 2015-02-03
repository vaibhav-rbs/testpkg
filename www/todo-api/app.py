#!/usr/bin/python

import os
from flask import Flask, jsonify, request,url_for,make_response
from werkzeug import secure_filename

app = Flask(__name__)
UPLOAD_FOLDER = '/var/www/testResults'
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

@app.route('/uploadTestResults/', methods=['GET', 'POST'])
def upload_file():
    if request.method == 'POST':
        file = request.files['file']
        if file:
            filename = secure_filename(file.filename)
            file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
            #return redirect(url_for('uploaded_file',filename=filename))
    return '''
    <!doctype html>
    <title>Upload new File</title>
    <h1>Upload new File</h1>
    <form action="" method=post enctype=multipart/form-data>
      <p><input type=file name=file>
         <input type=submit value=Upload>
    </form>
    '''
if __name__ == '__main__':
    app.run('10.75.18.21', 8080,debug = True)
