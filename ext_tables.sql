#
# Table structure for table 'tx_t3oodle_domain_model_poll'
#
CREATE TABLE tx_t3oodle_domain_model_poll (

    title varchar(255) DEFAULT '' NOT NULL,
    description text,
    link text,
    slug varchar(255) DEFAULT '' NOT NULL,
    type varchar(255) DEFAULT 'simple' NOT NULL,
    visibility varchar(255) DEFAULT 'public' NOT NULL,
    author varchar(255) DEFAULT '' NOT NULL,
    author_name varchar(255) DEFAULT '' NOT NULL,
    author_mail varchar(255) DEFAULT '' NOT NULL,
    author_ident varchar(128) DEFAULT '' NOT NULL,

    options int(11) unsigned DEFAULT '0' NOT NULL,

    suggest_mode_enabled smallint(5) unsigned DEFAULT '0' NOT NULL,
    is_suggest_mode_finished smallint(5) unsigned DEFAULT '0' NOT NULL,

    is_published smallint(5) unsigned DEFAULT '0' NOT NULL,
    publish_date int(10) unsigned DEFAULT '0',
    votes int(11) unsigned DEFAULT '0' NOT NULL,
    is_finished smallint(5) unsigned DEFAULT '0' NOT NULL,
    finish_date int(10) unsigned DEFAULT '0',
    final_option int(11) unsigned DEFAULT '0',

    setting_tristate_checkbox smallint(5) unsigned DEFAULT '0' NOT NULL,
    setting_max_votes_per_option int(11) unsigned DEFAULT '0',
    setting_min_votes_per_participant int(11) unsigned DEFAULT '0',
    setting_max_votes_per_participant int(11) unsigned DEFAULT '0',
    setting_secret_participants smallint(5) unsigned DEFAULT '0' NOT NULL,
    setting_secret_votings smallint(5) unsigned DEFAULT '0' NOT NULL,
    setting_super_secret_mode smallint(5) unsigned DEFAULT '0' NOT NULL,
    setting_voting_expires_date int(10) DEFAULT '0',
    setting_voting_expires_time int(10) DEFAULT '0'

);

#
# Table structure for table 'tx_t3oodle_domain_model_option'
#
CREATE TABLE tx_t3oodle_domain_model_option (

    poll int(11) unsigned DEFAULT '0' NOT NULL,

    name varchar(255) DEFAULT '' NOT NULL,
    creator varchar(255) DEFAULT '' NOT NULL,
    creator_name varchar(255) DEFAULT '' NOT NULL,
    creator_mail varchar(255) DEFAULT '' NOT NULL,
    creator_ident varchar(128) DEFAULT '' NOT NULL

);

#
# Table structure for table 'tx_t3oodle_domain_model_vote'
#
CREATE TABLE tx_t3oodle_domain_model_vote (

    participant varchar(255) DEFAULT '' NOT NULL,
    participant_name varchar(255) DEFAULT '' NOT NULL,
    participant_mail varchar(255) DEFAULT '' NOT NULL,
    participant_ident varchar(128) DEFAULT '' NOT NULL,
    option_values int(11) unsigned DEFAULT '0' NOT NULL,
    poll int(11) unsigned DEFAULT '0'

);


#
# Table structure for table 'tx_t3oodle_domain_model_optionvalue'
#
CREATE TABLE tx_t3oodle_domain_model_optionvalue (

    option int(11) unsigned DEFAULT '0',
    value varchar(255) DEFAULT '' NOT NULL,
    vote int(11) unsigned DEFAULT '0'

);
