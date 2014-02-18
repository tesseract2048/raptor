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

API_VER = 3
API_URL = "http://api.chongzhi.com"

class ChongzhiClient:
    def __init__(self, username, key):
        self.username = username
        self.key = key
        self.tsoffset = 0
        self.sysnow()

    def md5(self, str):
        return hashlib.md5(str).hexdigest()

    def urlencode(self, str):
        return urllib.quote(str).replace("/", "%2F")

    def sign(self, name, args, withkey=True):
        params = [(k, args[k]) for k in sorted(args.iterkeys())]
        segs = [("%s=%s" % (k, v)) for (k, v) in params]
        authstr = "%s?%s" % (name, "&".join(segs))
        name = 'sign'
        if withkey:
            authstr = "%s&%s" % (authstr, self.key)
            name = 'signkey'
        args[name] = self.md5(self.urlencode(authstr))

    def timestamp(self):
        return int(time.time()) + self.tsoffset

    def call(self, name, signmode, **args):
        args['username'] = self.username
        args['timestamp'] = self.timestamp()
        args['ver'] = API_VER
        args['format'] = 'json'
        if signmode > 0:
            self.sign(name, args, signmode == 2)
        url = '%s%s' % (API_URL, name)
        r = requests.request('POST', url, data=args)
        if r.status_code != 200:
            raise Exception("request failed with status code %d" % r.status_code)
        obj = json.loads(r.text)
        if 'sududa' not in obj:
            raise Exception("malformed response: %s" % r.text)
        return obj['sududa']

    def sysnow(self):
        ts = int(self.call('/api/sys_now', 0)['time'])
        self.tsoffset = ts - self.timestamp()
        return ts

    def product(self):
        return self.call('/api/product', 1, power=16)

    def productchannel(self):
        return self.call('/api/product_channel', 1)

    def productarea(self):
        return self.call('/api/product_area', 1)

    def userinfo(self):
        return self.call('/api/userinfo', 2)

    def sysphone(self, phone):
        return self.call('/api/sys_phone', 1, phone=phone)

    def recharge(self, orderid, productid, to, area, count):
        return self.call('/api/recharge', 2, orderid=orderid, productid=productid, to=to, area=area, count=count)

    def status(self, orderid):
        return self.call('/api/status', 1, orderid=orderid)
