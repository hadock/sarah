#!/usr/bin/python

import sys, getopt, os
#commands from line console
action = '';
status = '';
pin = '';

## For simplicity's sake, we'll create a string for our paths.
GPIO_MODE_PATH= os.path.normpath('/sys/devices/virtual/misc/gpio/mode/')
GPIO_PIN_PATH=os.path.normpath('/sys/devices/virtual/misc/gpio/pin/')
GPIO_FILENAME="gpio"

## create a couple of empty arrays to store the pointers for our files
pinMode = []
pinData = []

## Create a few strings for file I/O equivalence
HIGH = "1"
LOW =  "0"
INPUT = "0"
OUTPUT = "1"

## First, populate the arrays with file objects that we can use later.
for i in range(0,18):
  pinMode.append(os.path.join(GPIO_MODE_PATH, 'gpio'+str(i)))
  pinData.append(os.path.join(GPIO_PIN_PATH, 'gpio'+str(i)))

## Now, let's make all the pins outputs...
for dpin in pinMode:
  file = open(dpin, 'r+')  ## open the file in r/w mode
  file.write(OUTPUT)      ## set the mode of the pin
  file.close()

def main(argv):
    try:
       opts, args = getopt.getopt(argv,"ha:p:s:",["action=","pin=","status="])
    except getopt.GetoptError:
       print 'test.py -a|--action get|set -p|--pin {1-18} -s|--status on|off'
       sys.exit(2)
    for opt, arg in opts:
       if opt == "-h":
           print 'test.py -a|--action get|set -p|--pin {1-18} -s|--status on|off'
           sys.exit()
       elif opt in ("-a", "--action"):
          action = arg
       elif opt in ("-p", "--pin"):
          pin = arg
       elif opt in ("-s", "--status"):
          status = arg
    if action == "set":
        set_status(int(pin), status)
    elif action == "get":
        get_status(int(pin))
    else:
        print '-a option is mandatory (set|get)'
    

def set_status(pin_number, newstatus):
    try:
        if pin_number in range(0,18):
            print 'Updating the pin number: ', pin_number, ' to status: ', newstatus
            if newstatus == "on":
                file = open(pinData[pin_number], 'r+')
                file.write(HIGH)
                file.close()
            elif newstatus == "off":
                file = open(pinData[pin_number], 'r+')
                file.write(LOW)
                file.close()
            else:
               print '-s or --status option must to be (on|off) only' 
        else:
            print 'PIN_OUT_OF_RANGE'
    except getopt.GetoptError:
        print 'PIN_SET_ERROR'
        sys.exit()

def get_status(pin_number):
    try:
        if pin_number in range(0,18):
            #print 'Getting the status of the pin number ', pin_number
            file = open(pinData[pin_number], 'r').read()
            print file
    except getopt.GetoptError:
        print 'PIN_READING_ERROR'
        sys.exit()

if __name__ == "__main__":
   main(sys.argv[1:])