#!/usr/bin/python
# -*- coding: utf-8 -*-
"""
 @author:   hty0807@gmail.com
"""
import requests
import urllib
import hashlib
import time
import json

class CockpitClient:
    def __init__(self, url, instance):
        self.url = url
        self.instance = instance

    def call(self, name, **args):
        args['instance'] = self.instance
        url = '%s%s' % (self.url, name)
        r = requests.request('POST', url, data=args)
        if r.status_code != 200:
            raise Exception("request failed with status code %d" % r.status_code)
        obj = json.loads(r.text.encode('utf-8'))
        if not obj['success']:
            raise Exception("cockpit returned error: %s" % obj['error'])
        return obj

    def cleanslate(self):
        return self.call('/cleanslate')

    def pull(self):
        return self.call('/pull')

    def commit(self, id, result, retried, reason=None):
        return self.call('/commit', id=id, result=result, retried=retried, reason=reason)

    def drop(self, id):
        return self.call('/drop', id=id)
