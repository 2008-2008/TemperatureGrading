# TemperatureGrading
TemperatureGrading

The requirements are in the document belonging to a US Corporation.
We are choosing not to publish them here.

PACKAGE
-------
The package contains of 2 modules.
- tempserver.php: This is the API endpoint code. 
This API accepts an associative JSON array in the following format -
    - q: Degrees and unit in the question asked to student. The student has to
          convert these degrees to degrees in another unit. The degrees and unit
          need to be separated by a space. A valid example is "0 celcius".
          
          Degrees must be numeric.
          
          One letter abbreviation of the unit is allowed. Both capital and lower
          case letters are allowed.
    - a_unit: Temperature unit in which student's answer is expected.
          A valid example is "Kelvin".
          One letter abbreviation of the unit is allowed. Both capital and lower
          case letters are allowed.
     - a_degrees: Student's answer in degrees. This must be numeric. A valid
          example is "273.15".
          
     - grade: The grade assigned my the API. It can be one of these values.
          - Correct
          - Incorrect
          - Invalid
          
     The server keeps a log of requests and responses along with a timestamp. This
     log is written to file templog_server.txt
          
- tempclient.php: This is the test client. It is command-line. It takes input in this format

      SYNTAX: tempclient.php: "<q>" a_unit a_degrees [ <api url> ]
      
      The arguments q, a_unit and a_degrees are as described above.
      The <api url> is the url of the api. A valid example is
      "http://yourdomain.com/temperature/api". This argument is optional.
      If this argument is not specified, a default location where this api
      is already installed will be used. (You can find the default location
      in the code of this module.)
      
INSTALLATION
------------
To install, put the tempserver module at a location such as "http://yourdomain.com/temperature/".
Put the tempclient module on your desktop in a temporary directory. The execute the client using this
syntax -

php.exe tempclient.exe "<q>" a_unit a_degrees "http://yourdomain.com/temperature/tempserver.php"

Again, as above, if the api url is omitted, the default api location will be used.
The default server, may or may not, provide adequate performance. So, it is best to install at
your own test domain.


      
