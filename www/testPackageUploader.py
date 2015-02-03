#!/usr/bin/python
import json
from pprint import pprint
import subprocess
import os
from os.path import expanduser
import time
import os.path
import shutil
import tarfile
import zipfile
import smtplib
import sys
import logging
from git import Repo

def scriptLogger(args):
    logging.basicConfig(filename=expanduser('/tmp/scriptLog.log'),level=logging.DEBUG)
    logging.debug(args)

def processGitLink(gitLink):
    workingGitLinkUrl = "ssh://ca88git.am.mot-mobility.com:29418/"
    if not workingGitLinkUrl.split('/')[2] == gitLink.split('/')[2]:
        gitLink =  gitLink.replace(gitLink.split('/')[2],workingGitLinkUrl.split('/')[2])
    return gitLink

def checkFileDownload():
    os.system("scp autotest@144.189.164.130:/var/log/apache2/access.log /tmp/pingMeServeraccess.log") 

def sendNotificationText(server="smtp.gmail.com",userName="test88@motorola.com",password="2014Devops!",cellNumber="4084312689@txt.att.net",testLink="Test"):
    scriptLogger(server)
    server = smtplib.SMTP_SSL(server, 465)
    server.login(userName,password)
    server.sendmail(userName,cellNumber,testLink)

def sendTarFileToPingMeServer(locationOfTarFile="/var/www/tarPackage",nameOfTarFile="",phoneNumber="",email=""):
    fullPathOfFile = nameOfTarFile
    scpCommand = "scp "+ fullPathOfFile +" autotest@144.189.164.130:/home/autotest/untethered/"
    try:
        scriptLogger(scpCommand)
        os.popen(scpCommand)
        testLink= "\nhttp://144.189.164.130/" + nameOfTarFile.split('/')[-1]
        scriptLogger(testLink)
        sendNotificationText(testLink = testLink,cellNumber = email)
    except:
        print "something went wrong"

def makeTarFile(sourceDir,deviceID="",arcname="",phoneNumber="",email=""):
    if os.path.exists(expanduser("/home/autotest/untethered/tarPackage")):
        shutil.rmtree(expanduser("/home/autotest/untethered/tarPackage"))
    else:
        pass
    dstFolder = expanduser('/home/autotest/untethered/tarPackage')
    crtDstFolder = 'mkdir -p ' + dstFolder
    os.system(crtDstFolder)
    archiveName = str(time.time())+'_'+deviceID+'.tar' 
    print 'creating archive, '+archiveName
    out = tarfile.open(expanduser('/home/autotest/untethered/tarPackage/'+archiveName), mode='w')
    try:
        scriptLogger(sourceDir)
        out.add(sourceDir,arcname = arcname)
        tarFileLink = expanduser("/home/autotest/untethered/tarPackage/")+archiveName
        scriptLogger(tarFileLink)
        out.close()
    except Exception, e:
        print e
    
    sendTarFileToPingMeServer(nameOfTarFile=tarFileLink,phoneNumber=phoneNumber,email=email)
    #if file_exists:

    checkFileDownload()  

def getTest(getRequest):
    testLoc  = check(getRequest)
    deviceID = getRequest[1]
    phoneNumber = getRequest[2]
    email = getRequest[3]
    gitList= [];TestList = []; packageDir = "mkdir /home/autotest/untethered/testPackageDir"
    if os.path.exists(expanduser("/home/autotest/untethered/testPackageDir")):
        shutil.rmtree(expanduser("/home/autotest/untethered/testPackageDir"))
    else:
        pass
    originalDirectory = os.getcwd()
    gitrepo = ""
    os.system(packageDir)
    for test,gitLink in testLoc.items():
        gitLink = processGitLink(gitLink)
        arcname = gitLink.split('/')[-1].split('.')[0]
        print gitLink
        if gitLink not in gitList:
            gitRepo = expanduser("/home/autotest/untethered/tempGit_"+str(time.time()))
            gitLink = gitLink.replace('\\','')
            Repo.clone_from(gitLink, gitRepo)
            gitList.append(gitLink)
            testLink = gitRepo + test
            if os.path.isfile(testLink):
                relPath = test.rstrip(test.split('/')[-1])
                x = "mkdir -p /home/autotest/untethered/testPackageDir"+relPath
                os.system(x)
                y = "/home/autotest/untethered/testPackageDir" + relPath
                cpTest = "cp "+testLink+" "+ expanduser(y) 
                print cpTest
                os.system(cpTest)
        else:
            print "git link already cloned, skipping, checking for test cases."
            testLink = gitRepo + test 
            if os.path.isfile(testLink):
                relPath = test.rstrip(test.split('/')[-1])
                x = "mkdir -p /home/autotest/untethered/testPackageDir"+relPath
                os.system(x)
                y = "/home/autotest/untethered/testPackageDir" + relPath
                cpTest = "cp "+testLink+" "+ expanduser(y) 
                print cpTest
                os.system(cpTest)
    makeTarFile(expanduser("/home/autotest/untethered/testPackageDir"),deviceID,arcname=arcname,phoneNumber=phoneNumber,email=email)
    os.system("cd /home/autotest/untethered/; rm -rf tempGit_*;cd -; rm -rf /home/autotest/untethered/testPackageDir")


def check(userName):
    userName = userName[0]
    p = subprocess.Popen(["ls", "/var/www/tempdata/testexec"], stdout=subprocess.PIPE)
    out,err = p.communicate()
    out = out.split('\n')[:-1]
    for fileName in out:
        if userName in fileName:
            filePath = "/var/www/tempdata/testexec/"+fileName
            json_data=open(filePath)
            data = json.load(json_data)
    testLoc = searchForGitTest(data)
    curDict = os.popen("pwd")
    os.system("cd /tmp/")
    return testLoc

def searchForGitTest(data):
    aux = {};auxList= []
    for idx in range(len(data["rows"])):
        scriptPath = data["rows"][idx]["scriptPath"]
        gitPath = data["rows"][idx]["gitPath"]
        aux[scriptPath] = gitPath
    return aux

if __name__ == "__main__":
    getTest(sys.argv[1:])
