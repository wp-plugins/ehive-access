<?php
/*
	Copyright (C) 2012 Vernon Systems Limited

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), 
	to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, 
	and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, 
	WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
define ("EHIVE_API_ROOT_DIR", dirname(__FILE__) );

define ("VERSION_ID", "/v2");

require_once EHIVE_API_ROOT_DIR.'/exceptions/EHiveApiException.php';
require_once EHIVE_API_ROOT_DIR.'/transport/Transport.php';

class EHiveApi {
	
	protected $transport;
	
	/**
	 * String $clientId - eHive API key client Id
	 * 
	 * String $clientSecret - eHive API key client secret
	 * 
	 * String $trackingId - eHive API key tracking Id.
	 * 
	 * String $oauthToken - The OAuthToken vendered by a previous request API request.
	 * 
	 * function $oauthCredentialsCallback - A function that takes a single String parameter for a vendered OAauth Token.
	 *    exmple:
	 *      function oauthTokenCallback($oauthToken) {
	 *         // persist the vendered oauthToken for reuse with the next instantiation of an EHiveApi class.
	 *      }
	 * 
	 * array $memcacheServers - array of hosts and ports for Memcached services. When null memcache is disabled. 
	 *    examples: 
	 *      Memcached on the same server - array('localhost:11211') 
	 *      Memcached distributed on two servers - array('192.168.1.4:11211', '192.168.1.5:11211')  
	 * 
	 * number $memcacheExpiry - cache expiry time in seconds.
	 */
	public function __construct( $clientId=null, $clientSecret=null, $trackingId=null, $oauthToken=null, $oauthTokenCallback=null, $memcacheServers=null, $memcacheExpiry=300) {
		$this->transport = new Transport($clientId, $clientSecret, $trackingId, $oauthToken, $oauthTokenCallback, $memcacheServers, $memcacheExpiry);
	}
	
	function getConfiguration() {
		return $this->configuration;
	}
	
	
	//
	// Get Accounts
	//
	public function getAccount($accountId) {
		require_once EHIVE_API_ROOT_DIR.'/dao/accounts/AccountsDao.php';
		$accountsDao = new AccountsDao($this->transport);
		$account = $accountsDao->getAccount($accountId);
		return $account;
	}
	
	public function getAccountInCommunity($communityId, $accountId) {
		require_once EHIVE_API_ROOT_DIR.'/dao/accounts/AccountsDao.php';
		$accountsDao = new AccountsDao($this->transport);
		$account = $accountsDao->getAccountInCommunity($communityId, $accountId);
		return $account;
	}

	public function getAccountsInEHive( $query, $sort, $direction, $offset=0, $limit=12 ) {
		require_once EHIVE_API_ROOT_DIR.'/dao/accounts/AccountsDao.php';
		$accountsDao = new AccountsDao($this->transport);
		$accountsCollection = $accountsDao->getAccountsInEHive( $query, $sort, $direction, $offset, $limit );
		return $accountsCollection;
	}
	
	public function getAccountsInCommunity( $communityId, $query, $sort, $direction, $offset=0, $limit=12 ) {
		require_once EHIVE_API_ROOT_DIR.'/dao/accounts/AccountsDao.php';
		$accountsDao = new AccountsDao($this->transport);
		$accountsCollection = $accountsDao->getAccountsInCommunity( $communityId, $query, $sort, $direction, $offset, $limit );
		return $accountsCollection;
	}
	

	//
	//	Get Communities
	//
	public function getCommunitiesModeratoredByAccount($accountId) {
		require_once EHIVE_API_ROOT_DIR.'/dao/communities/CommunitiesDao.php';
		$communitiesDao = new CommunitiesDao($this->transport);
		$communitiesCollection = $communitiesDao->getCommunitiesModeratedByAccount($accountId);
		return $communitiesCollection;
	}
	
	public function getCommunitiesInEHive( $query, $sort, $direction, $offset=0, $limit=12 ) {
		require_once EHIVE_API_ROOT_DIR.'/dao/communities/CommunitiesDao.php';
		$communitiesDao = new CommunitiesDao($this->transport);
		$communitiesCollection = $communitiesDao->getCommunitiesInEHive( $query, $sort, $direction, $offset, $limit );
		return $communitiesCollection;
	}
	
		
	//
	//	Get ObjectRecords
	//	
	public function getObjectRecord($objectRecordId) {
		require_once EHIVE_API_ROOT_DIR.'/dao/objectrecords/ObjectRecordsDao.php';
		$objectRecordsDao = new ObjectRecordsDao($this->transport);
		$objectRecords = $objectRecordsDao->getObjectRecord($objectRecordId);
		return $objectRecords;
	}
	
	public function getObjectRecordsInEHive( $query, $hasImages=false, $sort, $direction, $offset=0, $limit=12 ) {
		require_once EHIVE_API_ROOT_DIR.'/dao/objectrecords/ObjectRecordsDao.php';
		$objectRecordsDao = new ObjectRecordsDao($this->transport);
		$objectRecordsCollection = $objectRecordsDao->getObjectRecordsInEHive( $query, $hasImages, $sort, $direction, $offset, $limit );
		return $objectRecordsCollection;
	}
	
	public function getObjectRecordsInAccount( $accountId, $query, $hasImages=false, $sort, $direction, $offset=0, $limit=12, $content="public" ) {
		require_once EHIVE_API_ROOT_DIR.'/dao/objectrecords/ObjectRecordsDao.php';
		$objectRecordsDao = new ObjectRecordsDao($this->transport);
		$objectRecordsCollection = $objectRecordsDao->getObjectRecordsInAccount( $accountId, $query, $hasImages, $sort, $direction, $offset, $limit, $content );
		return $objectRecordsCollection;		
	}
	
	public function getObjectRecordsInCommunity( $communityId, $query, $hasImages=false, $sort, $direction, $offset=0, $limit=12 ) {
		require_once EHIVE_API_ROOT_DIR.'/dao/objectrecords/ObjectRecordsDao.php';
		$objectRecordsDao = new ObjectRecordsDao($this->transport);
		$objectRecordsCollection = $objectRecordsDao->getObjectRecordsInCommunity( $communityId, $query, $hasImages, $sort, $direction, $offset, $limit );
		return $objectRecordsCollection;
	}

	public function getObjectRecordsInAccountInCommunity( $communityId, $accountId, $query, $hasImages=false, $sort, $direction, $offset=0, $limit=12 ) {
		require_once EHIVE_API_ROOT_DIR.'/dao/objectrecords/ObjectRecordsDao.php';
		$objectRecordsDao = new ObjectRecordsDao($this->transport);
		$objectRecordsCollection = $objectRecordsDao->getObjectRecordsInAccountInCommunity( $communityId, $accountId, $query, $hasImages, $sort, $direction, $offset, $limit );
		return $objectRecordsCollection;
	}
	
	
	//
	// Get Interesting Object Records
	//
	public function getInterestingObjectRecordsInEHive($hasImages=false, $catalogueType="", $offset=0, $limit=12){
		require_once EHIVE_API_ROOT_DIR.'/dao/interestingobjectrecords/InterestingObjectRecordsDao.php';
		$interestingObjectRecordsDao = new InterestingObjectRecordsDao($this->transport);
		$interestingObjectRecords = $interestingObjectRecordsDao->getInterestingObjectRecordsInEHive($hasImages, $catalogueType, $offset, $limit);
		return $interestingObjectRecords;		
	}
	
	public function getInterestingObjectRecordsInAccount($accountId, $catalogueType="", $hasImages=false, $offset=0, $limit=12, $content="public"){
		require_once EHIVE_API_ROOT_DIR.'/dao/interestingobjectrecords/InterestingObjectRecordsDao.php';
		$interestingObjectRecordsDao = new InterestingObjectRecordsDao($this->transport);
		$interestingObjectRecords = $interestingObjectRecordsDao->getInterestingObjectRecordsInAccount($accountId, $catalogueType, $hasImages, $offset, $limit, $content);
		return $interestingObjectRecords;
	}
	
	public function getInterestingObjectRecordsInCommunity($communityId, $catalogueType="", $hasImages=false, $offset=0, $limit=12){
		require_once EHIVE_API_ROOT_DIR.'/dao/interestingobjectrecords/InterestingObjectRecordsDao.php';
		$interestingObjectRecordsDao = new InterestingObjectRecordsDao($this->transport);
		$interestingObjectRecords = $interestingObjectRecordsDao->getInterestingObjectRecordsInCommunity($communityId, $catalogueType, $hasImages, $offset, $limit);
		return $interestingObjectRecords;
	}
	
	public function getInterestingObjectRecordsInAccountInCommunity($communityId, $accountId, $catalogueType="", $hasImages=false, $offset=0, $limit=12){
		require_once EHIVE_API_ROOT_DIR.'/dao/interestingobjectrecords/InterestingObjectRecordsDao.php';
		$interestingObjectRecordsDao = new InterestingObjectRecordsDao($this->transport);
		$interestingObjectRecords = $interestingObjectRecordsDao->getInterestingObjectRecordsInAccountInCommunity($communityId, $accountId, $catalogueType, $hasImages, $offset, $limit);
		return $interestingObjectRecords;
	}
	
	
	//
	// Get Popular Object Records
	//
	public function getPopularObjectRecordsInEHive($catalogueType="", $hasImages=false, $offset=0, $limit=12){
		require_once EHIVE_API_ROOT_DIR.'/dao/popularobjectrecords/PopularObjectRecordsDao.php';
		$popularObjectRecordsDao = new PopularObjectRecordsDao($this->transport);
		$popularObjectRecords = $popularObjectRecordsDao->getPopularObjectRecordsInEHive($catalogueType, $hasImages, $offset, $limit);
		return $popularObjectRecords;
	}
	
	public function getPopularObjectRecordsInAccount($accountId, $catalogueType="", $hasImages=false, $offset=0, $limit=12, $content="public"){
		require_once EHIVE_API_ROOT_DIR.'/dao/popularobjectrecords/PopularObjectRecordsDao.php';
		$popularObjectRecordsDao = new PopularObjectRecordsDao($this->transport);
		$popularObjectRecords = $popularObjectRecordsDao->getPopularObjectRecordsInAccount($accountId, $catalogueType, $hasImages, $offset, $limit, $content);
		return $popularObjectRecords;
	}
	
	public function getPopularObjectRecordsInCommunity($communityId, $catalogueType="", $hasImages=false, $offset=0, $limit=12){
		require_once EHIVE_API_ROOT_DIR.'/dao/popularobjectrecords/PopularObjectRecordsDao.php';
		$popularObjectRecordsDao = new PopularObjectRecordsDao($this->transport);
		$popularObjectRecords = $popularObjectRecordsDao->getPopularObjectRecordsInCommunity($communityId, $catalogueType, $hasImages, $offset, $limit);
		return $popularObjectRecords;
	}
	
	public function getPopularObjectRecordsInAccountInCommunity($communityId, $accountId, $catalogueType="", $hasImages=false, $offset=0, $limit=12){
		require_once EHIVE_API_ROOT_DIR.'/dao/popularobjectrecords/PopularObjectRecordsDao.php';
		$popularObjectRecordsDao = new PopularObjectRecordsDao($this->transport);
		$popularObjectRecords = $popularObjectRecordsDao->getPopularObjectRecordsInAccountInCommunity($communityId, $accountId, $catalogueType, $hasImages, $offset, $limit);
		return $popularObjectRecords;
	}
	
	
	//
	// Get Recent Object Records
	//
	public function getRecentObjectRecordsInEHive($catalogueType="", $hasImages=false, $offset=0, $limit=12){
		require_once EHIVE_API_ROOT_DIR.'/dao/recentobjectrecords/RecentObjectRecordsDao.php';
		$recentObjectRecordsDao = new RecentObjectRecordsDao($this->transport);
		$recentObjectRecords = $recentObjectRecordsDao->getRecentObjectRecordsInEHive($catalogueType, $hasImages, $offset, $limit);
		return $recentObjectRecords;
	}
	
	public function getRecentObjectRecordsInAccount($accountId, $catalogueType="", $hasImages=false, $offset=0, $limit=12, $content="public"){
		require_once EHIVE_API_ROOT_DIR.'/dao/recentobjectrecords/RecentObjectRecordsDao.php';
		$recentObjectRecordsDao = new RecentObjectRecordsDao($this->transport);
		$recentObjectRecords = $recentObjectRecordsDao->getRecentObjectRecordsInAccount($accountId, $catalogueType, $hasImages, $offset, $limit, $content);
		return $recentObjectRecords;
	}
	
	public function getRecentObjectRecordsInCommunity($communityId, $catalogueType="", $hasImages=false, $offset=0, $limit=12){
		require_once EHIVE_API_ROOT_DIR.'/dao/recentobjectrecords/RecentObjectRecordsDao.php';
		$recentObjectRecordsDao = new RecentObjectRecordsDao($this->transport);
		$recentObjectRecords = $recentObjectRecordsDao->getRecentObjectRecordsInCommunity($communityId, $catalogueType, $hasImages, $offset, $limit);
		return $recentObjectRecords;
	}
	
	public function getRecentObjectRecordsInAccountInCommunity($communityId, $accountId, $catalogueType="", $hasImages=false, $offset=0, $limit=12){
		require_once EHIVE_API_ROOT_DIR.'/dao/recentobjectrecords/RecentObjectRecordsDao.php';
		$recentObjectRecordsDao = new RecentObjectRecordsDao($this->transport);
		$recentObjectRecords = $recentObjectRecordsDao->getRecentObjectRecordsInAccountInCommunity($communityId, $accountId, $catalogueType, $hasImages, $offset, $limit);
		return $recentObjectRecords;
	}

	
	//
	// Object Comments
	//
	public function getObjectRecordComments($objectRecordId, $offset, $limit) {
		require_once EHIVE_API_ROOT_DIR.'/dao/comments/CommentsDao.php';
		$commentsDao = new CommentsDao($this->transport);
		$comments = $commentsDao->getObjectRecordComments($objectRecordId, $offset, $limit);
		return $comments;
	}
	
	public function addObjectRecordComment($objectRecordId, $comment) {
		require_once EHIVE_API_ROOT_DIR.'/dao/comments/CommentsDao.php';
		$commentsDao = new CommentsDao($this->transport);
		$comment = $commentsDao->addObjectRecordComment($objectRecordId, $comment);
		return $comment;
	}
	
	
	//
	//	Object Record Tags
	//
	public function getObjectRecordTags($objectRecordId) {
		require_once EHIVE_API_ROOT_DIR.'/dao/objectrecordtags/ObjectRecordTagsDao.php';
		$objectRecordTagsDao = new ObjectRecordTagsDao($this->transport);
		$objectRecordTags = $objectRecordTagsDao->getObjectRecordTags($objectRecordId);
		return $objectRecordTags;
	}
	
	public function addObjectRecordTag($objectRecordId, $objectRecordTag) {
		require_once EHIVE_API_ROOT_DIR.'/dao/objectrecordtags/ObjectRecordTagsDao.php';
		$objectRecordTagsDao = new ObjectRecordTagsDao($this->transport);
		$objectRecordTag = $objectRecordTagsDao->addObjectRecordTag($objectRecordId, $objectRecordTag);
		return $objectRecordTag;
	}
	
	public function deleteObjectRecordTag($objectRecordId, $tag) {
		require_once EHIVE_API_ROOT_DIR.'/dao/objectrecordtags/ObjectRecordTagsDao.php';
		$objectRecordTagsDao = new ObjectRecordTagsDao($this->transport);
		$objectRecordTag = $objectRecordTagsDao->deleteObjectRecordTag($objectRecordId, $tag);
		return $objectRecordTag;
	}

	
	//
	// Tag Clouds
	// 
	public function getTagCloudInEHive($limit) {
		require_once EHIVE_API_ROOT_DIR.'/dao/tagcloud/TagCloudDao.php';
		$tagCloudDao = new TagCloudDao($this->transport);
		$tagCloud = $tagCloudDao->getTagCloudInEHive($limit);
		return $tagCloud;
	}
	
	public function getTagCloudInAccount($accountId, $limit) {
		require_once EHIVE_API_ROOT_DIR.'/dao/tagcloud/TagCloudDao.php';
		$tagCloudDao = new TagCloudDao($this->transport);
		$tagCloud = $tagCloudDao->getTagCloudInAccount($accountId, $limit);
		return $tagCloud;
	}
	
	public function getTagCloudInCommunity($communityId, $limit) {
		require_once EHIVE_API_ROOT_DIR.'/dao/tagcloud/TagCloudDao.php';
		$tagCloudDao = new TagCloudDao($this->transport);
		$tagCloud = $tagCloudDao->getTagCloudInCommunity($communityId, $limit);
		return $tagCloud;
	}
}