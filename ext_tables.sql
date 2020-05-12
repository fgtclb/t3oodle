#
# Table structure for table 'tx_t3oodle_domain_model_poll'
#
CREATE TABLE tx_t3oodle_domain_model_poll (

	title varchar(255) DEFAULT '' NOT NULL,
	slug varchar(255) DEFAULT '' NOT NULL,
	visibility varchar(255) DEFAULT 'public' NOT NULL,
	author varchar(255) DEFAULT '' NOT NULL,
	author_user varchar(255) DEFAULT '' NOT NULL,
	options int(11) unsigned DEFAULT '0' NOT NULL,

);

#
# Table structure for table 'tx_t3oodle_domain_model_option'
#
CREATE TABLE tx_t3oodle_domain_model_option (

	poll int(11) unsigned DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	selected smallint(5) unsigned DEFAULT '0' NOT NULL,
	votes int(11) unsigned DEFAULT '0' NOT NULL,
	parent int(11) unsigned DEFAULT '0',

);

#
# Table structure for table 'tx_t3oodle_domain_model_vote'
#
CREATE TABLE tx_t3oodle_domain_model_vote (

	tx_option int(11) unsigned DEFAULT '0' NOT NULL,

	value varchar(255) DEFAULT '' NOT NULL,
	parent int(11) unsigned DEFAULT '0',

);

#
# Table structure for table 'tx_t3oodle_domain_model_option'
#
CREATE TABLE tx_t3oodle_domain_model_option (

	poll int(11) unsigned DEFAULT '0' NOT NULL,

);

#
# Table structure for table 'tx_t3oodle_domain_model_vote'
#
CREATE TABLE tx_t3oodle_domain_model_vote (

	tx_option int(11) unsigned DEFAULT '0' NOT NULL,

);
