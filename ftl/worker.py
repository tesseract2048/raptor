#!/usr/bin/python
# -*- coding: utf-8 -*-
"""
 @author:   hty0807@gmail.com
"""
import time
import logging
from chongzhiclient import ChongzhiClient
from cockpitclient import CockpitClient

MAX_RETRIES = 3
SUCCESS, FAILED, HOLDING = range(0, 3)

class Worker:
    def __init__(self, **args):
        logging.info('FTL drive %s reporting on duty.' % args['name'])
        logging.info('Initializing chongzhi client...')
        self.chongzhi = ChongzhiClient(args['user'], args['key'])
        logging.info('Initializing cockpit client...')
        self.cockpit = CockpitClient(args['cockpit'], args['name'])
        logging.info('Cleaning slate...')
        self.cockpit.cleanslate()
        logging.info('Worker initialized successfully.')
        self.asyncjobs = []

    def async(self, orderid, job):
        self.asyncjobs.append((orderid, job))

    def detectstatus(self, resp):
        status = int(resp['status'])
        if 'tips' in resp:
            logging.info('Tips: %s' % resp['tips'])
        if status == -9 or status == 10:
            return FAILED, resp['tips']
        if status == -1 or status == 0:
            self.async(orderid, job)
            return HOLDING, resp['tips']
        return SUCCESS, resp['tips']

    def fulfill(self, job):
        orderid = "%sv%sv%s" % (job['ticket']['number'], job['id'], job['retried'])
        productid = job['product_id']
        to = job['to']
        area = job['area']
        count = job['ticket']['count']
        logging.info('Fulfilling: Job [orderid = %s, productid = %s, area = %s, count = %s]' % (orderid, productid, area, count))
        resp = self.chongzhi.recharge(orderid, productid, to, area, count)
        logging.info('Fulfilling Response: %s' % str(resp))
        return self.detectstatus(resp)

    def commit(self, job, status, reason=None):
        logging.info('Committing job %s with status %s...' % (job['id'], status))
        if status == HOLDING:
            return
        retried = int(job['retried'])
        if status == FAILED:
            retried += 1
            result = 0
        else:
            result = 1
        if retried >= MAX_RETRIES:
            result = -1
        self.cockpit.commit(job['id'], result, retried, reason)

    def pull(self):
        job = self.cockpit.pull()['response']
        if not job:
            return
        logging.info('Job %s retrieved.' % job['id'])
        status, reason = self.fulfill(job)
        self.commit(job, status, reason)

    def poll(self):
        logging.info('Polling jobs, %d items...' % len(self.asyncjobs))
        new_asyncjobs = []
        for (orderid, job) in self.asyncjobs:
            resp = self.chongzhi.status(orderid)
            status, reason = self.detectstatus(resp)
            if status == HOLDING:
                new_asyncjobs.append((orderid, job))
            else:
                self.commit(job, status, reason)
        self.asyncjobs = new_asyncjobs
        logging.info('Jobs polled, %d item left.' % len(self.asyncjobs))

    def watch(self):
        logging.info('Worker started watching.')
        tick = 0
        while True:
            tick += 1
            if tick % 30 == 0:
                self.poll()
                tick = 0
            else:
                self.pull()
            time.sleep(1)
