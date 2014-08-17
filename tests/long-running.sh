#!/bin/bash -ex
(
  echo "Beginning script..."
  
  # prints current date and time to a file every 1 second for roughly 1 hour
  i=1
  while [ $i -le 3000 ]
  do
    ((i++))
    date >> /var/www/cronkeep/output-$$.txt
    sleep 1
  done 

  echo "Ending."
  
) 200>/var/lock/long-running.lock
