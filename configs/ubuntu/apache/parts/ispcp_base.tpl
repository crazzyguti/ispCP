# httpd Data BEGIN.

#
# wget-hack prevention
#

<IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteCond %{HTTP_USER_AGENT} ^LWP::Simple
    RewriteRule ^/.* http://%{REMOTE_ADDR}/ [L,E=nolog:1]
</IfModule>

#
# Log processing.
#

LogFormat "%B" traff
LogFormat "%v %b %h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" ispcplog

CustomLog "| /var/www/ispcp/engine/ispcp-apache-logger" ispcplog
ErrorLog "| /var/www/ispcp/engine/ispcp-apache-logger -e"

#
# mod_cband configuration
#

<IfModule mod_cband.c>
    CBandScoreFlushPeriod 10
    CBandRandomPulse On
</IfModule>

#
# let the customer decide what charset he likes to use
#

AddDefaultCharset Off

#
# Header End
#

# httpd [{IP}] virtual host entry BEGIN.
# httpd [{IP}] virtual host entry END.

# httpd Data END.
