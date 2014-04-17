# OpenEoto Courses
OpenEoto Courses is an add-on for the open source CMS concrete5. It is part of the openeoto project: http://openeoto.org

Warning: This add-on is a software prototype. It is not recommend to use it in production environments.

## Installation

1. Copy the directory to packages/ folder of concrete5
2. Login to the dashboard of your concrete5 instance
3. Install open courses package
4. Add "zip" to allowed file types in ""System&Settings->Permissions->Allowed File Types"
5. have fun! :-)

Detailed installation instructions can be found here:
http://openeoto.org/ (English website coming soon)

### Troubleshooting

The package will automatically try to set new directory in files/ dir. If there will be errors, please make the following directories writable for the executing user.

* files/open_courses/
* files/open_courses/media/
* files/open_courses/tmp/

### Requirements

* PHP >= 5.3
* Concrete5 Version >= 5.6
* The ZipArchive class (PHP 5 >= 5.2.0, PECL zip >= 1.1.0) http://php.net/manual/en/class.ziparchive.php
 

