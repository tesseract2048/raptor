#!/usr/bin/python
# -*- coding: utf-8 -*-
"""
 @author:   hty0807@gmail.com
"""
import logging
import requests
from optparse import OptionParser
from worker import Worker

logging.basicConfig(level=logging.DEBUG,
    format='%(asctime)s %(filename)s[line:%(lineno)d] %(levelname)s: %(message)s',
    datefmt='%a, %d %b %Y %H:%M:%S')

# Silence requests
requests_logger = logging.getLogger('requests')
requests_logger.setLevel(logging.CRITICAL)

# Parse command line options
parser = OptionParser() 
parser.add_option("-n", "--name", action="store", 
                  dest="name", 
                  help="instance name") 
parser.add_option("-c", "--cockpit", action="store", 
                  dest="cockpit", 
                  help="cockpit api address")
parser.add_option("-u", "--user", action="store", 
                  dest="user", 
                  help="user for chongzhi api")
parser.add_option("-k", "--key", action="store", 
                  dest="key", 
                  help="key for chongzhi api")

(options, args) = parser.parse_args()

params = vars(options)
for k in params:
    if params[k] == None:
        raise Exception("%s must not be None" % k)
        exit

# Initialize worker
worker = Worker(**params)
worker.watch()