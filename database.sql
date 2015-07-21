
#
# This is the database structure and sample configuration
# for the Web-Enabled LETS System developed by the Ottawa
# LETS Technology Committee
#
# April 26, 2002


# --------------------------------------------------------
#
# Table structure for table 'account'
#

CREATE TABLE account (
   AccountID int(11) NOT NULL auto_increment,
   AccountName varchar(30) NOT NULL,
   AccountTypeID tinyint(3) unsigned DEFAULT '0',
   AccountRenewalDate date,
   AccountCreated date,
   AccountIsFeeExempt tinyint(3) unsigned DEFAULT '0',
   AccountCreditLimit decimal(6,2) DEFAULT '100.00' NOT NULL,
   AccountStatus set('OK','Suspended','Suspended from Sale','Suspended from Buy','Closed') DEFAULT 'OK' NOT NULL,
   PRIMARY KEY (AccountID)
);

#
# Dumping data for table 'account'
#


# --------------------------------------------------------
#
# Table structure for table 'accounttypeoptions'
#

CREATE TABLE accounttypeoptions (
   AccountTypeID tinyint(3) unsigned NOT NULL auto_increment,
   AccountTypeName varchar(20),
   AccountTypeMaxMembers tinyint(3) unsigned DEFAULT '0',
   AccountTypeCost decimal(5,2) unsigned DEFAULT '0.00',
   AccountTypeRenewalCost decimal(5,2) unsigned DEFAULT '0.00',
   AccountTypeNumFreeAds tinyint(1) unsigned DEFAULT '3' NOT NULL,
   AccountTypeExtraAdCost decimal(4,2) unsigned DEFAULT '2.00' NOT NULL,
   AccountTypeSaleTransactionFee decimal(5,3) DEFAULT '0.020' NOT NULL,
   AccountTypeBuyTransactionFee decimal(5,3) DEFAULT '0.020' NOT NULL,
   PRIMARY KEY (AccountTypeID),
   UNIQUE AccountTypeID (AccountTypeID),
   KEY AccountTypeID_2 (AccountTypeID)
);

#
# Dumping data for table 'accounttypeoptions'
#

INSERT INTO accounttypeoptions VALUES ( '1', 'Family', '4', '20.00', '10.00', '3', '2.00', '0.020', '0.020');
INSERT INTO accounttypeoptions VALUES ( '2', 'Personal', '1', '15.00', '10.00', '3', '2.00', '0.020', '0.020');
INSERT INTO accounttypeoptions VALUES ( '3', 'Business', '10', '25.00', '10.00', '5', '2.00', '0.020', '0.020');
INSERT INTO accounttypeoptions VALUES ( '4', 'Associate', '1', '0.00', '0.00', '0', '0.00', '0.000', '0.000');

# --------------------------------------------------------
#
# Table structure for table 'adcategories'
#

CREATE TABLE adcategories (
   CategoryID int(10) unsigned NOT NULL auto_increment,
   CategoryName char(25) NOT NULL,
   CategoryDescription char(255),
   HeadingID int(10) unsigned DEFAULT '0' NOT NULL,
   PRIMARY KEY (CategoryID),
   KEY CategoryName (CategoryName)
);

#
# Dumping data for table 'adcategories'
#

INSERT INTO adcategories VALUES ( '1', 'Long-Term', '', '1');
INSERT INTO adcategories VALUES ( '2', 'Short-Term', '', '1');
INSERT INTO adcategories VALUES ( '3', 'Retreat &amp; Recreation', '', '1');
INSERT INTO adcategories VALUES ( '4', 'Arts', '', '2');
INSERT INTO adcategories VALUES ( '5', 'Crafts', '', '2');
INSERT INTO adcategories VALUES ( '6', 'Music', '', '2');
INSERT INTO adcategories VALUES ( '7', 'Other', '', '2');
INSERT INTO adcategories VALUES ( '8', 'Rides', '', '3');
INSERT INTO adcategories VALUES ( '9', 'Vehicles', '', '3');
INSERT INTO adcategories VALUES ( '10', 'Parts and Labour', '', '3');
INSERT INTO adcategories VALUES ( '11', 'Marine', '', '3');
INSERT INTO adcategories VALUES ( '12', 'Bicycle Equipment', '', '3');
INSERT INTO adcategories VALUES ( '13', 'Bicycle Service', '', '3');
INSERT INTO adcategories VALUES ( '14', 'Other', '', '3');
INSERT INTO adcategories VALUES ( '15', 'Child Care', '', '4');
INSERT INTO adcategories VALUES ( '16', 'Adult Care', '', '4');
INSERT INTO adcategories VALUES ( '17', 'Pet Care', '', '4');
INSERT INTO adcategories VALUES ( '18', 'Other', '', '4');
INSERT INTO adcategories VALUES ( '19', 'Academic Help', '', '5');
INSERT INTO adcategories VALUES ( '20', 'Culinary Arts', '', '5');
INSERT INTO adcategories VALUES ( '21', 'Language', '', '5');
INSERT INTO adcategories VALUES ( '22', 'Computing', '', '5');
INSERT INTO adcategories VALUES ( '23', 'Well-being', '', '5');
INSERT INTO adcategories VALUES ( '24', 'Music', '', '5');
INSERT INTO adcategories VALUES ( '25', 'Other', '', '5');
INSERT INTO adcategories VALUES ( '26', 'Plants', '', '6');
INSERT INTO adcategories VALUES ( '27', 'Cleaning', '', '6');
INSERT INTO adcategories VALUES ( '28', 'General Labour', '', '6');
INSERT INTO adcategories VALUES ( '29', 'Carpentry', '', '6');
INSERT INTO adcategories VALUES ( '30', 'Plumbing', '', '6');
INSERT INTO adcategories VALUES ( '31', 'Consultation', '', '6');
INSERT INTO adcategories VALUES ( '32', 'Tools and Equipment', '', '6');
INSERT INTO adcategories VALUES ( '33', 'Renovation &amp; Repair', '', '6');
INSERT INTO adcategories VALUES ( '34', 'Other', '', '6');
INSERT INTO adcategories VALUES ( '35', 'Furniture &amp; Appliances', '', '7');
INSERT INTO adcategories VALUES ( '36', 'Clothing', '', '7');
INSERT INTO adcategories VALUES ( '37', 'Toys', '', '7');
INSERT INTO adcategories VALUES ( '38', 'Computer Equipment', '', '7');
INSERT INTO adcategories VALUES ( '39', 'Sports Equipment', '', '7');
INSERT INTO adcategories VALUES ( '40', 'General Household', '', '7');
INSERT INTO adcategories VALUES ( '41', 'Other', '', '7');
INSERT INTO adcategories VALUES ( '42', 'Financial', '', '8');
INSERT INTO adcategories VALUES ( '43', 'Research', '', '8');
INSERT INTO adcategories VALUES ( '44', 'Consulting', '', '8');
INSERT INTO adcategories VALUES ( '45', 'Health', '', '8');
INSERT INTO adcategories VALUES ( '46', 'Counselling', '', '8');
INSERT INTO adcategories VALUES ( '47', 'Massage &amp; Reiki', '', '8');
INSERT INTO adcategories VALUES ( '48', 'Snow Removal', '', '8');
INSERT INTO adcategories VALUES ( '49', 'Personal', '', '8');
INSERT INTO adcategories VALUES ( '50', 'Office', '', '8');
INSERT INTO adcategories VALUES ( '51', 'Other', '', '8');
INSERT INTO adcategories VALUES ( '52', 'Baking', '', '9');
INSERT INTO adcategories VALUES ( '53', 'Catering', '', '9');
INSERT INTO adcategories VALUES ( '54', 'Meal Preparation', '', '9');
INSERT INTO adcategories VALUES ( '55', 'Vegetarian', '', '9');
INSERT INTO adcategories VALUES ( '56', 'Preserves', '', '9');
INSERT INTO adcategories VALUES ( '57', 'Organics', '', '9');
INSERT INTO adcategories VALUES ( '58', 'Fresh Produce', '', '9');
INSERT INTO adcategories VALUES ( '59', 'Other', '', '9');
INSERT INTO adcategories VALUES ( '60', 'Equipment', '', '10');
INSERT INTO adcategories VALUES ( '61', 'Instruction', '', '10');
INSERT INTO adcategories VALUES ( '62', 'Other', '', '10');
INSERT INTO adcategories VALUES ( '63', 'General', '', '11');
INSERT INTO adcategories VALUES ( '64', 'Painting', '', '11');
INSERT INTO adcategories VALUES ( '65', 'Plumbing', '', '11');
INSERT INTO adcategories VALUES ( '66', 'Mechanical', '', '11');
INSERT INTO adcategories VALUES ( '67', 'Renovation', '', '11');
INSERT INTO adcategories VALUES ( '68', 'Electrical', '', '11');
INSERT INTO adcategories VALUES ( '69', 'Other', '', '11');

# --------------------------------------------------------
#
# Table structure for table 'adheadings'
#

CREATE TABLE adheadings (
   HeadingID int(10) unsigned NOT NULL auto_increment,
   HeadingName char(20) NOT NULL,
   PRIMARY KEY (HeadingID),
   UNIQUE HeadingName (HeadingName)
);

#
# Dumping data for table 'adheadings'
#

INSERT INTO adheadings VALUES ( '1', 'Accomodation');
INSERT INTO adheadings VALUES ( '2', 'Creative Arts');
INSERT INTO adheadings VALUES ( '3', 'Transportation');
INSERT INTO adheadings VALUES ( '4', 'Caregiving Services');
INSERT INTO adheadings VALUES ( '5', 'Training &amp; Education');
INSERT INTO adheadings VALUES ( '6', 'Home and Garden');
INSERT INTO adheadings VALUES ( '7', 'Goods');
INSERT INTO adheadings VALUES ( '8', 'Services');
INSERT INTO adheadings VALUES ( '9', 'Food');
INSERT INTO adheadings VALUES ( '10', 'Recreation');
INSERT INTO adheadings VALUES ( '11', 'Trades and Labour');
INSERT INTO adheadings VALUES ( '12', 'Computers');
INSERT INTO adheadings VALUES ( '13', 'Other');

# --------------------------------------------------------
#
# Table structure for table 'adminactions'
#

CREATE TABLE adminactions (
   Time timestamp(14),
   MemberID int(11) DEFAULT '0' NOT NULL,
   Action char(80) NOT NULL,
   KEY Time (Time, MemberID)
);

#
# Dumping data for table 'adminactions'
#


# --------------------------------------------------------
#
# Table structure for table 'administration'
#

CREATE TABLE administration (
   AdminPassword char(12) NOT NULL,
   DataPassword char(12) NOT NULL,
   UpperCreditLimitFactor decimal(3,1) DEFAULT '1.0' NOT NULL,
   TradeEntryBy set('buyer','seller') NOT NULL,
   SetupFee decimal(4,2) DEFAULT '0.00' NOT NULL
);

#
# Dumping data for table 'administration'
#

INSERT INTO administration VALUES ( 'password', 'data', '10.0', 'buyer', '0.00');

# --------------------------------------------------------
#
# Table structure for table 'adminlogins'
#

CREATE TABLE adminlogins (
   MemberID int(11) DEFAULT '0' NOT NULL,
   AuthorizationCode char(12) NOT NULL,
   LoginTime datetime,
   AdminType char(6) NOT NULL,
   UNIQUE LoginTime (LoginTime),
   KEY MemberID (MemberID)
);

#
# Dumping data for table 'adminlogins'
#


# --------------------------------------------------------
#
# Table structure for table 'advertisements'
#

CREATE TABLE advertisements (
   AdID int(10) unsigned NOT NULL auto_increment,
   AccountID int(10) unsigned DEFAULT '0' NOT NULL,
   CategoryID int(10) unsigned DEFAULT '0' NOT NULL,
   CategoryID2 int(10) unsigned,
   CategoryID3 int(10) unsigned,
   TradeType char(1) NOT NULL,
   AdBeginDate date DEFAULT '0000-00-00' NOT NULL,
   AdExpiryDate date DEFAULT '0000-00-00' NOT NULL,
   AdName char(30) NOT NULL,
   AdDescription char(255),
   PRIMARY KEY (AdID),
   UNIQUE AdID (AdID),
   KEY AdID_2 (AdID)
);

#
# Dumping data for table 'advertisements'
#


# --------------------------------------------------------
#
# Table structure for table 'badtradewarnings'
#

CREATE TABLE badtradewarnings (
   GivenToAccountID int(11) DEFAULT '0' NOT NULL,
   AboutAccountID int(11) DEFAULT '0' NOT NULL,
   WarningDate date DEFAULT '0000-00-00' NOT NULL,
   Cause char(20) NOT NULL
);

#
# Dumping data for table 'badtradewarnings'
#


# --------------------------------------------------------
#
# Table structure for table 'bulletins'
#

CREATE TABLE bulletins (
   BulletinID int(11) NOT NULL auto_increment,
   Priority set('Low','High') NOT NULL,
   BeginDate date DEFAULT '0000-00-00' NOT NULL,
   EndDate date DEFAULT '0000-00-00' NOT NULL,
   Title varchar(100) NOT NULL,
   Text text NOT NULL,
   PRIMARY KEY (BulletinID),
   UNIQUE BulletinID (BulletinID),
   KEY BulletinID_2 (BulletinID, Priority)
);

#
# Dumping data for table 'bulletins'
#

# --------------------------------------------------------
#
# Table structure for table 'cheques'
# 

CREATE TABLE cheques (
   ChequeID in(11) NOT NULL auto_increment,
   AccountID int(11) NOT NULL default '0',
   IssueDate date NOT NULL default '0000-00-00',
   ExpiryDate date NOT NULL default '0000-00-00',
   TransactionID in(11) default NULL,
   PRIMARY KEY  (ChequeID)
);

#
# Dumping data for table 'cheques'
#

# --------------------------------------------------------
#
# Table structure for table 'creditlimits'
#

CREATE TABLE creditlimits (
   AccountTypeID tinyint(4) DEFAULT '0' NOT NULL,
   TradeVolume decimal(7,2) DEFAULT '0.00' NOT NULL,
   CreditLimit decimal(7,2) DEFAULT '0.00' NOT NULL,
   KEY AccountTypeID (AccountTypeID, TradeVolume)
);

#
# Dumping data for table 'creditlimits'
#

INSERT INTO creditlimits VALUES ( '1', '0.00', '100.00');
INSERT INTO creditlimits VALUES ( '1', '300.00', '300.00');
INSERT INTO creditlimits VALUES ( '1', '1000.00', '500.00');
INSERT INTO creditlimits VALUES ( '2', '0.00', '100.00');
INSERT INTO creditlimits VALUES ( '2', '300.00', '300.00');
INSERT INTO creditlimits VALUES ( '2', '1000.00', '500.00');
INSERT INTO creditlimits VALUES ( '3', '0.00', '100.00');
INSERT INTO creditlimits VALUES ( '3', '300.00', '300.00');
INSERT INTO creditlimits VALUES ( '3', '1000.00', '500.00');
INSERT INTO creditlimits VALUES ( '4', '0.00', '0.00');

# --------------------------------------------------------
#
# Table structure for table 'deliverymethodoptions'
#

CREATE TABLE deliverymethodoptions (
   DeliveryMethodID tinyint(3) unsigned NOT NULL auto_increment,
   DeliveryMethodName varchar(15) DEFAULT '0',
   DeliveryMethodDescription text,
   DeliveryMethodCost decimal(4,2) unsigned DEFAULT '0.00',
   PRIMARY KEY (DeliveryMethodID),
   UNIQUE DeliveryMethodID (DeliveryMethodID),
   KEY DeliveryMethodID_2 (DeliveryMethodID)
);

#
# Dumping data for table 'deliverymethodoptions'
#

INSERT INTO deliverymethodoptions VALUES ( '1', 'NoDelivery', 'Receive No Mailings to this Address', '1.00');
INSERT INTO deliverymethodoptions VALUES ( '2', 'Email', 'Mailings are emailed to this account', '2.00');
INSERT INTO deliverymethodoptions VALUES ( '3', 'Direct Mail', NULL, '3.00');
INSERT INTO deliverymethodoptions VALUES ( '4', 'All Methods', NULL, '4.00');

# --------------------------------------------------------
#
# Table structure for table 'infopages'
#

CREATE TABLE infopages (
   PageID smallint(6) NOT NULL auto_increment,
   Parent smallint(6),
   Title varchar(60) NOT NULL,
   Menu set('Yes','No') DEFAULT 'No' NOT NULL,
   MenuPlacement set('Top','Bottom') DEFAULT 'Bottom' NOT NULL,
   Priority tinyint(4) DEFAULT '0' NOT NULL,
   Data text NOT NULL,
   MainPage set('Yes','No') DEFAULT 'No' NOT NULL,
   PRIMARY KEY (PageID),
   KEY Parent (Parent)
);

#
# Dumping data for table 'infopages'
#

INSERT INTO infopages VALUES ( '1', '0', 'Welcome to Web-Enabled LETS', 'Yes', 'Top', '0', 'This is where the welcome and introduction would go','Yes');

# --------------------------------------------------------
#
# Table structure for table 'member'
#

CREATE TABLE member (
   MemberID int(10) unsigned NOT NULL auto_increment,
   MemberFirstName varchar(15) NOT NULL,
   MemberMiddleName varchar(15),
   MemberLastName varchar(15) NOT NULL,
   MailingAddress1 varchar(50),
   MailingAddress2 varchar(50),
   MailingCity varchar(20),
   MailingProvince char(2),
   MailingPostalCode varchar(7),
   StreetAddress1 varchar(50),
   StreetAddress2 varchar(50),
   StreetCity varchar(20),
   StreetProvince char(2),
   StreetPostalCode varchar(7),
   HomeNumber varchar(20),
   OtherNumber varchar(20),
   EmailAddress varchar(50),
   DeliveryMethodID tinyint(3) unsigned DEFAULT '0',
   LoginID varchar(10),
   Password varchar(10),
   Profile longtext,
   ProfileEnabled tinyint(1) unsigned DEFAULT '0' NOT NULL,
   HomeURL varchar(128),
   PriorLogin tinyint(1) DEFAULT '0' NOT NULL,
   PRIMARY KEY (MemberID)
);

#
# Dumping data for table 'member'
#


# --------------------------------------------------------
#
# Table structure for table 'membertoaccountlink'
#

CREATE TABLE membertoaccountlink (
   AccountID int(11) unsigned DEFAULT '0' NOT NULL,
   MemberID int(11) unsigned DEFAULT '0' NOT NULL,
   PrimaryContact tinyint(1) unsigned DEFAULT '0',
   PRIMARY KEY (AccountID, MemberID)
);

#
# Dumping data for table 'membertoaccountlink'
#



# --------------------------------------------------------
#
# Table structure for table 'transactions'
#

CREATE TABLE transactions (
   Reference int(11) NOT NULL auto_increment,
   TransactionID int(10) DEFAULT '0' NOT NULL,
   TradeDate date DEFAULT '0000-00-00' NOT NULL,
   AccountID int(11) DEFAULT '0' NOT NULL,
   Amount decimal(6,2) DEFAULT '0.00' NOT NULL,
   Description char(40) NOT NULL,
   CurrentBalance decimal(7,2) DEFAULT '0.00' NOT NULL,
   OtherAccountID int(10) DEFAULT '0' NOT NULL,
   SystemFee tinyint(1) DEFAULT '0',
   KEY Reference (Reference),
   KEY TransactionID (TransactionID),
   KEY AccountID (AccountID)
);

#
# Dumping data for table 'transactions'
#



# --------------------------------------------------------
#
# Table structure for table 'transidlookup'
#

CREATE TABLE transidlookup (
   TransactionID int(10) unsigned NOT NULL auto_increment,
   Time char(10),
   MemberID int(11) unsigned DEFAULT '0' NOT NULL,
   PRIMARY KEY (TransactionID)
);

#
# Dumping data for table 'transidlookup'
#

