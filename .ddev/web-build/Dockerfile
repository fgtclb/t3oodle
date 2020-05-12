ARG BASE_IMAGE
FROM $BASE_IMAGE

WORKDIR /var/www/html

RUN rm index.html
RUN composer init -n
RUN composer config extra.typo3/cms.web-dir .
RUN composer req t3/cms:'^9.5' jigal/t3adminer:'^9.4' --no-progress --no-suggest -n

ARG uid
ARG gid
RUN chown -R $uid:$gid /var/www/html
