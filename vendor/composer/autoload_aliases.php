<?php



namespace MilliCache\Deps {

    class AliasAutoloader
    {
        private string $includeFilePath;

        private array $autoloadAliases = array (
  'MilliRules\\Actions\\BaseAction' => 
  array (
    'type' => 'class',
    'classname' => 'BaseAction',
    'isabstract' => true,
    'namespace' => 'MilliRules\\Actions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Actions\\BaseAction',
    'implements' => 
    array (
      0 => 'MilliRules\\Actions\\ActionInterface',
    ),
  ),
  'MilliRules\\Actions\\Callback' => 
  array (
    'type' => 'class',
    'classname' => 'Callback',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Actions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Actions\\Callback',
    'implements' => 
    array (
      0 => 'MilliRules\\Actions\\ActionInterface',
    ),
  ),
  'MilliRules\\ArgumentValue' => 
  array (
    'type' => 'class',
    'classname' => 'ArgumentValue',
    'isabstract' => false,
    'namespace' => 'MilliRules',
    'extends' => 'MilliCache\\Deps\\MilliRules\\ArgumentValue',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Builders\\ActionBuilder' => 
  array (
    'type' => 'class',
    'classname' => 'ActionBuilder',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Builders',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Builders\\ActionBuilder',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Builders\\ConditionBuilder' => 
  array (
    'type' => 'class',
    'classname' => 'ConditionBuilder',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Builders',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Builders\\ConditionBuilder',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Conditions\\BaseCondition' => 
  array (
    'type' => 'class',
    'classname' => 'BaseCondition',
    'isabstract' => true,
    'namespace' => 'MilliRules\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Conditions\\BaseCondition',
    'implements' => 
    array (
      0 => 'MilliRules\\Conditions\\ConditionInterface',
    ),
  ),
  'MilliRules\\Conditions\\Callback' => 
  array (
    'type' => 'class',
    'classname' => 'Callback',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Conditions\\Callback',
    'implements' => 
    array (
      0 => 'MilliRules\\Conditions\\ConditionInterface',
    ),
  ),
  'MilliRules\\Context' => 
  array (
    'type' => 'class',
    'classname' => 'Context',
    'isabstract' => false,
    'namespace' => 'MilliRules',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Context',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Contexts\\BaseContext' => 
  array (
    'type' => 'class',
    'classname' => 'BaseContext',
    'isabstract' => true,
    'namespace' => 'MilliRules\\Contexts',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Contexts\\BaseContext',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Logger' => 
  array (
    'type' => 'class',
    'classname' => 'Logger',
    'isabstract' => false,
    'namespace' => 'MilliRules',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Logger',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\MilliRules' => 
  array (
    'type' => 'class',
    'classname' => 'MilliRules',
    'isabstract' => false,
    'namespace' => 'MilliRules',
    'extends' => 'MilliCache\\Deps\\MilliRules\\MilliRules',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\BasePackage' => 
  array (
    'type' => 'class',
    'classname' => 'BasePackage',
    'isabstract' => true,
    'namespace' => 'MilliRules\\Packages',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\BasePackage',
    'implements' => 
    array (
      0 => 'MilliRules\\Packages\\PackageInterface',
    ),
  ),
  'MilliRules\\Packages\\PHP\\Conditions\\Constant' => 
  array (
    'type' => 'class',
    'classname' => 'Constant',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\PHP\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PHP\\Conditions\\Constant',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\PHP\\Conditions\\Cookie' => 
  array (
    'type' => 'class',
    'classname' => 'Cookie',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\PHP\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PHP\\Conditions\\Cookie',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\PHP\\Conditions\\RequestHeader' => 
  array (
    'type' => 'class',
    'classname' => 'RequestHeader',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\PHP\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PHP\\Conditions\\RequestHeader',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\PHP\\Conditions\\RequestMethod' => 
  array (
    'type' => 'class',
    'classname' => 'RequestMethod',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\PHP\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PHP\\Conditions\\RequestMethod',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\PHP\\Conditions\\RequestParam' => 
  array (
    'type' => 'class',
    'classname' => 'RequestParam',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\PHP\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PHP\\Conditions\\RequestParam',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\PHP\\Conditions\\RequestUrl' => 
  array (
    'type' => 'class',
    'classname' => 'RequestUrl',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\PHP\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PHP\\Conditions\\RequestUrl',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\PHP\\Contexts\\Cookie' => 
  array (
    'type' => 'class',
    'classname' => 'Cookie',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\PHP\\Contexts',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PHP\\Contexts\\Cookie',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\PHP\\Contexts\\Param' => 
  array (
    'type' => 'class',
    'classname' => 'Param',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\PHP\\Contexts',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PHP\\Contexts\\Param',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\PHP\\Contexts\\Request' => 
  array (
    'type' => 'class',
    'classname' => 'Request',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\PHP\\Contexts',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PHP\\Contexts\\Request',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\PHP\\Package' => 
  array (
    'type' => 'class',
    'classname' => 'Package',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\PHP',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PHP\\Package',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\PHP\\PlaceholderResolver' => 
  array (
    'type' => 'class',
    'classname' => 'PlaceholderResolver',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\PHP',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PHP\\PlaceholderResolver',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\PackageManager' => 
  array (
    'type' => 'class',
    'classname' => 'PackageManager',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\PackageManager',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\Author' => 
  array (
    'type' => 'class',
    'classname' => 'Author',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\Author',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\Category' => 
  array (
    'type' => 'class',
    'classname' => 'Category',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\Category',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\IsConditional' => 
  array (
    'type' => 'class',
    'classname' => 'IsConditional',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\IsConditional',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\Post' => 
  array (
    'type' => 'class',
    'classname' => 'Post',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\Post',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\PostParent' => 
  array (
    'type' => 'class',
    'classname' => 'PostParent',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\PostParent',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\PostStatus' => 
  array (
    'type' => 'class',
    'classname' => 'PostStatus',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\PostStatus',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\PostType' => 
  array (
    'type' => 'class',
    'classname' => 'PostType',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\PostType',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\QueryVar' => 
  array (
    'type' => 'class',
    'classname' => 'QueryVar',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\QueryVar',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\Tag' => 
  array (
    'type' => 'class',
    'classname' => 'Tag',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\Tag',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\Taxonomy' => 
  array (
    'type' => 'class',
    'classname' => 'Taxonomy',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\Taxonomy',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\Template' => 
  array (
    'type' => 'class',
    'classname' => 'Template',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\Template',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\Term' => 
  array (
    'type' => 'class',
    'classname' => 'Term',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\Term',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\UserRole' => 
  array (
    'type' => 'class',
    'classname' => 'UserRole',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\UserRole',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Conditions\\WpEnvironment' => 
  array (
    'type' => 'class',
    'classname' => 'WpEnvironment',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Conditions',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\WpEnvironment',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Contexts\\Post' => 
  array (
    'type' => 'class',
    'classname' => 'Post',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Contexts',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Contexts\\Post',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Contexts\\Query' => 
  array (
    'type' => 'class',
    'classname' => 'Query',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Contexts',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Contexts\\Query',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Contexts\\Term' => 
  array (
    'type' => 'class',
    'classname' => 'Term',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Contexts',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Contexts\\Term',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Contexts\\User' => 
  array (
    'type' => 'class',
    'classname' => 'User',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress\\Contexts',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Contexts\\User',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\Package' => 
  array (
    'type' => 'class',
    'classname' => 'Package',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Package',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Packages\\WordPress\\PlaceholderResolver' => 
  array (
    'type' => 'class',
    'classname' => 'PlaceholderResolver',
    'isabstract' => false,
    'namespace' => 'MilliRules\\Packages\\WordPress',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\PlaceholderResolver',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\PlaceholderResolver' => 
  array (
    'type' => 'class',
    'classname' => 'PlaceholderResolver',
    'isabstract' => false,
    'namespace' => 'MilliRules',
    'extends' => 'MilliCache\\Deps\\MilliRules\\PlaceholderResolver',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\RuleEngine' => 
  array (
    'type' => 'class',
    'classname' => 'RuleEngine',
    'isabstract' => false,
    'namespace' => 'MilliRules',
    'extends' => 'MilliCache\\Deps\\MilliRules\\RuleEngine',
    'implements' => 
    array (
    ),
  ),
  'MilliRules\\Rules' => 
  array (
    'type' => 'class',
    'classname' => 'Rules',
    'isabstract' => false,
    'namespace' => 'MilliRules',
    'extends' => 'MilliCache\\Deps\\MilliRules\\Rules',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Autoloader' => 
  array (
    'type' => 'class',
    'classname' => 'Autoloader',
    'isabstract' => false,
    'namespace' => 'Predis',
    'extends' => 'MilliCache\\Deps\\Predis\\Autoloader',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Client' => 
  array (
    'type' => 'class',
    'classname' => 'Client',
    'isabstract' => false,
    'namespace' => 'Predis',
    'extends' => 'MilliCache\\Deps\\Predis\\Client',
    'implements' => 
    array (
      0 => 'Predis\\ClientInterface',
      1 => 'IteratorAggregate',
    ),
  ),
  'Predis\\ClientConfiguration' => 
  array (
    'type' => 'class',
    'classname' => 'ClientConfiguration',
    'isabstract' => false,
    'namespace' => 'Predis',
    'extends' => 'MilliCache\\Deps\\Predis\\ClientConfiguration',
    'implements' => 
    array (
    ),
  ),
  'Predis\\ClientException' => 
  array (
    'type' => 'class',
    'classname' => 'ClientException',
    'isabstract' => false,
    'namespace' => 'Predis',
    'extends' => 'MilliCache\\Deps\\Predis\\ClientException',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Cluster\\ClusterStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'ClusterStrategy',
    'isabstract' => true,
    'namespace' => 'Predis\\Cluster',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\ClusterStrategy',
    'implements' => 
    array (
      0 => 'Predis\\Cluster\\StrategyInterface',
    ),
  ),
  'Predis\\Cluster\\Distributor\\EmptyRingException' => 
  array (
    'type' => 'class',
    'classname' => 'EmptyRingException',
    'isabstract' => false,
    'namespace' => 'Predis\\Cluster\\Distributor',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\Distributor\\EmptyRingException',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Cluster\\Distributor\\HashRing' => 
  array (
    'type' => 'class',
    'classname' => 'HashRing',
    'isabstract' => false,
    'namespace' => 'Predis\\Cluster\\Distributor',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\Distributor\\HashRing',
    'implements' => 
    array (
      0 => 'Predis\\Cluster\\Distributor\\DistributorInterface',
      1 => 'Predis\\Cluster\\Hash\\HashGeneratorInterface',
    ),
  ),
  'Predis\\Cluster\\Distributor\\KetamaRing' => 
  array (
    'type' => 'class',
    'classname' => 'KetamaRing',
    'isabstract' => false,
    'namespace' => 'Predis\\Cluster\\Distributor',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\Distributor\\KetamaRing',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Cluster\\Hash\\CRC16' => 
  array (
    'type' => 'class',
    'classname' => 'CRC16',
    'isabstract' => false,
    'namespace' => 'Predis\\Cluster\\Hash',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\Hash\\CRC16',
    'implements' => 
    array (
      0 => 'Predis\\Cluster\\Hash\\HashGeneratorInterface',
    ),
  ),
  'Predis\\Cluster\\Hash\\PhpiredisCRC16' => 
  array (
    'type' => 'class',
    'classname' => 'PhpiredisCRC16',
    'isabstract' => false,
    'namespace' => 'Predis\\Cluster\\Hash',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\Hash\\PhpiredisCRC16',
    'implements' => 
    array (
      0 => 'Predis\\Cluster\\Hash\\HashGeneratorInterface',
    ),
  ),
  'Predis\\Cluster\\NullSlotRange' => 
  array (
    'type' => 'class',
    'classname' => 'NullSlotRange',
    'isabstract' => false,
    'namespace' => 'Predis\\Cluster',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\NullSlotRange',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Cluster\\PredisStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'PredisStrategy',
    'isabstract' => false,
    'namespace' => 'Predis\\Cluster',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\PredisStrategy',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Cluster\\RedisStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'RedisStrategy',
    'isabstract' => false,
    'namespace' => 'Predis\\Cluster',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\RedisStrategy',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Cluster\\SimpleSlotMap' => 
  array (
    'type' => 'class',
    'classname' => 'SimpleSlotMap',
    'isabstract' => false,
    'namespace' => 'Predis\\Cluster',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\SimpleSlotMap',
    'implements' => 
    array (
      0 => 'ArrayAccess',
      1 => 'IteratorAggregate',
      2 => 'Countable',
    ),
  ),
  'Predis\\Cluster\\SlotMap' => 
  array (
    'type' => 'class',
    'classname' => 'SlotMap',
    'isabstract' => false,
    'namespace' => 'Predis\\Cluster',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\SlotMap',
    'implements' => 
    array (
      0 => 'ArrayAccess',
      1 => 'IteratorAggregate',
      2 => 'Countable',
    ),
  ),
  'Predis\\Cluster\\SlotRange' => 
  array (
    'type' => 'class',
    'classname' => 'SlotRange',
    'isabstract' => false,
    'namespace' => 'Predis\\Cluster',
    'extends' => 'MilliCache\\Deps\\Predis\\Cluster\\SlotRange',
    'implements' => 
    array (
      0 => 'Countable',
    ),
  ),
  'Predis\\Collection\\Iterator\\CursorBasedIterator' => 
  array (
    'type' => 'class',
    'classname' => 'CursorBasedIterator',
    'isabstract' => true,
    'namespace' => 'Predis\\Collection\\Iterator',
    'extends' => 'MilliCache\\Deps\\Predis\\Collection\\Iterator\\CursorBasedIterator',
    'implements' => 
    array (
      0 => 'Iterator',
    ),
  ),
  'Predis\\Collection\\Iterator\\HashKey' => 
  array (
    'type' => 'class',
    'classname' => 'HashKey',
    'isabstract' => false,
    'namespace' => 'Predis\\Collection\\Iterator',
    'extends' => 'MilliCache\\Deps\\Predis\\Collection\\Iterator\\HashKey',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Collection\\Iterator\\Keyspace' => 
  array (
    'type' => 'class',
    'classname' => 'Keyspace',
    'isabstract' => false,
    'namespace' => 'Predis\\Collection\\Iterator',
    'extends' => 'MilliCache\\Deps\\Predis\\Collection\\Iterator\\Keyspace',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Collection\\Iterator\\ListKey' => 
  array (
    'type' => 'class',
    'classname' => 'ListKey',
    'isabstract' => false,
    'namespace' => 'Predis\\Collection\\Iterator',
    'extends' => 'MilliCache\\Deps\\Predis\\Collection\\Iterator\\ListKey',
    'implements' => 
    array (
      0 => 'Iterator',
    ),
  ),
  'Predis\\Collection\\Iterator\\SetKey' => 
  array (
    'type' => 'class',
    'classname' => 'SetKey',
    'isabstract' => false,
    'namespace' => 'Predis\\Collection\\Iterator',
    'extends' => 'MilliCache\\Deps\\Predis\\Collection\\Iterator\\SetKey',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Collection\\Iterator\\SortedSetKey' => 
  array (
    'type' => 'class',
    'classname' => 'SortedSetKey',
    'isabstract' => false,
    'namespace' => 'Predis\\Collection\\Iterator',
    'extends' => 'MilliCache\\Deps\\Predis\\Collection\\Iterator\\SortedSetKey',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Geospatial\\AbstractBy' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractBy',
    'isabstract' => true,
    'namespace' => 'Predis\\Command\\Argument\\Geospatial',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Geospatial\\AbstractBy',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\Geospatial\\ByInterface',
    ),
  ),
  'Predis\\Command\\Argument\\Geospatial\\ByBox' => 
  array (
    'type' => 'class',
    'classname' => 'ByBox',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Geospatial',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Geospatial\\ByBox',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Geospatial\\ByRadius' => 
  array (
    'type' => 'class',
    'classname' => 'ByRadius',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Geospatial',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Geospatial\\ByRadius',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Geospatial\\FromLonLat' => 
  array (
    'type' => 'class',
    'classname' => 'FromLonLat',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Geospatial',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Geospatial\\FromLonLat',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\Geospatial\\FromInterface',
    ),
  ),
  'Predis\\Command\\Argument\\Geospatial\\FromMember' => 
  array (
    'type' => 'class',
    'classname' => 'FromMember',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Geospatial',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Geospatial\\FromMember',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\Geospatial\\FromInterface',
    ),
  ),
  'Predis\\Command\\Argument\\Search\\AggregateArguments' => 
  array (
    'type' => 'class',
    'classname' => 'AggregateArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\AggregateArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\AlterArguments' => 
  array (
    'type' => 'class',
    'classname' => 'AlterArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\AlterArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\CommonArguments' => 
  array (
    'type' => 'class',
    'classname' => 'CommonArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\CommonArguments',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\ArrayableArgument',
    ),
  ),
  'Predis\\Command\\Argument\\Search\\CreateArguments' => 
  array (
    'type' => 'class',
    'classname' => 'CreateArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\CreateArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\CursorArguments' => 
  array (
    'type' => 'class',
    'classname' => 'CursorArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\CursorArguments',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\ArrayableArgument',
    ),
  ),
  'Predis\\Command\\Argument\\Search\\DropArguments' => 
  array (
    'type' => 'class',
    'classname' => 'DropArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\DropArguments',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\ArrayableArgument',
    ),
  ),
  'Predis\\Command\\Argument\\Search\\ExplainArguments' => 
  array (
    'type' => 'class',
    'classname' => 'ExplainArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\ExplainArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\ProfileArguments' => 
  array (
    'type' => 'class',
    'classname' => 'ProfileArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\ProfileArguments',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\ArrayableArgument',
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SchemaFields\\AbstractField' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractField',
    'isabstract' => true,
    'namespace' => 'Predis\\Command\\Argument\\Search\\SchemaFields',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SchemaFields\\AbstractField',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\Search\\SchemaFields\\FieldInterface',
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SchemaFields\\GeoField' => 
  array (
    'type' => 'class',
    'classname' => 'GeoField',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search\\SchemaFields',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SchemaFields\\GeoField',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SchemaFields\\GeoShapeField' => 
  array (
    'type' => 'class',
    'classname' => 'GeoShapeField',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search\\SchemaFields',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SchemaFields\\GeoShapeField',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SchemaFields\\NumericField' => 
  array (
    'type' => 'class',
    'classname' => 'NumericField',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search\\SchemaFields',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SchemaFields\\NumericField',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SchemaFields\\TagField' => 
  array (
    'type' => 'class',
    'classname' => 'TagField',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search\\SchemaFields',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SchemaFields\\TagField',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SchemaFields\\TextField' => 
  array (
    'type' => 'class',
    'classname' => 'TextField',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search\\SchemaFields',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SchemaFields\\TextField',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SchemaFields\\VectorField' => 
  array (
    'type' => 'class',
    'classname' => 'VectorField',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search\\SchemaFields',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SchemaFields\\VectorField',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SearchArguments' => 
  array (
    'type' => 'class',
    'classname' => 'SearchArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SearchArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SpellcheckArguments' => 
  array (
    'type' => 'class',
    'classname' => 'SpellcheckArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SpellcheckArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SugAddArguments' => 
  array (
    'type' => 'class',
    'classname' => 'SugAddArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SugAddArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SugGetArguments' => 
  array (
    'type' => 'class',
    'classname' => 'SugGetArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SugGetArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SynUpdateArguments' => 
  array (
    'type' => 'class',
    'classname' => 'SynUpdateArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SynUpdateArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\Server\\LimitOffsetCount' => 
  array (
    'type' => 'class',
    'classname' => 'LimitOffsetCount',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Server',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Server\\LimitOffsetCount',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\Server\\LimitInterface',
    ),
  ),
  'Predis\\Command\\Argument\\Server\\To' => 
  array (
    'type' => 'class',
    'classname' => 'To',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\Server',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Server\\To',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\ArrayableArgument',
    ),
  ),
  'Predis\\Command\\Argument\\TimeSeries\\AddArguments' => 
  array (
    'type' => 'class',
    'classname' => 'AddArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\TimeSeries\\AddArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\TimeSeries\\AlterArguments' => 
  array (
    'type' => 'class',
    'classname' => 'AlterArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\TimeSeries\\AlterArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\TimeSeries\\CommonArguments' => 
  array (
    'type' => 'class',
    'classname' => 'CommonArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\TimeSeries\\CommonArguments',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\ArrayableArgument',
    ),
  ),
  'Predis\\Command\\Argument\\TimeSeries\\CreateArguments' => 
  array (
    'type' => 'class',
    'classname' => 'CreateArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\TimeSeries\\CreateArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\TimeSeries\\DecrByArguments' => 
  array (
    'type' => 'class',
    'classname' => 'DecrByArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\TimeSeries\\DecrByArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\TimeSeries\\GetArguments' => 
  array (
    'type' => 'class',
    'classname' => 'GetArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\TimeSeries\\GetArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\TimeSeries\\IncrByArguments' => 
  array (
    'type' => 'class',
    'classname' => 'IncrByArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\TimeSeries\\IncrByArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\TimeSeries\\InfoArguments' => 
  array (
    'type' => 'class',
    'classname' => 'InfoArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\TimeSeries\\InfoArguments',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Argument\\ArrayableArgument',
    ),
  ),
  'Predis\\Command\\Argument\\TimeSeries\\MGetArguments' => 
  array (
    'type' => 'class',
    'classname' => 'MGetArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\TimeSeries\\MGetArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\TimeSeries\\MRangeArguments' => 
  array (
    'type' => 'class',
    'classname' => 'MRangeArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\TimeSeries\\MRangeArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Argument\\TimeSeries\\RangeArguments' => 
  array (
    'type' => 'class',
    'classname' => 'RangeArguments',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Argument\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Argument\\TimeSeries\\RangeArguments',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Command' => 
  array (
    'type' => 'class',
    'classname' => 'Command',
    'isabstract' => true,
    'namespace' => 'Predis\\Command',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Command',
    'implements' => 
    array (
      0 => 'Predis\\Command\\CommandInterface',
    ),
  ),
  'Predis\\Command\\Factory' => 
  array (
    'type' => 'class',
    'classname' => 'Factory',
    'isabstract' => true,
    'namespace' => 'Predis\\Command',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Factory',
    'implements' => 
    array (
      0 => 'Predis\\Command\\FactoryInterface',
    ),
  ),
  'Predis\\Command\\Processor\\KeyPrefixProcessor' => 
  array (
    'type' => 'class',
    'classname' => 'KeyPrefixProcessor',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Processor',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Processor\\KeyPrefixProcessor',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Processor\\ProcessorInterface',
    ),
  ),
  'Predis\\Command\\Processor\\ProcessorChain' => 
  array (
    'type' => 'class',
    'classname' => 'ProcessorChain',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Processor',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Processor\\ProcessorChain',
    'implements' => 
    array (
      0 => 'ArrayAccess',
      1 => 'Predis\\Command\\Processor\\ProcessorInterface',
    ),
  ),
  'Predis\\Command\\RawCommand' => 
  array (
    'type' => 'class',
    'classname' => 'RawCommand',
    'isabstract' => false,
    'namespace' => 'Predis\\Command',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\RawCommand',
    'implements' => 
    array (
      0 => 'Predis\\Command\\CommandInterface',
    ),
  ),
  'Predis\\Command\\RawFactory' => 
  array (
    'type' => 'class',
    'classname' => 'RawFactory',
    'isabstract' => false,
    'namespace' => 'Predis\\Command',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\RawFactory',
    'implements' => 
    array (
      0 => 'Predis\\Command\\FactoryInterface',
    ),
  ),
  'Predis\\Command\\Redis\\ACL' => 
  array (
    'type' => 'class',
    'classname' => 'ACL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ACL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\APPEND' => 
  array (
    'type' => 'class',
    'classname' => 'APPEND',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\APPEND',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\AUTH' => 
  array (
    'type' => 'class',
    'classname' => 'AUTH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\AUTH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\AbstractCommand\\BZPOPBase' => 
  array (
    'type' => 'class',
    'classname' => 'BZPOPBase',
    'isabstract' => true,
    'namespace' => 'Predis\\Command\\Redis\\AbstractCommand',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\AbstractCommand\\BZPOPBase',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BGREWRITEAOF' => 
  array (
    'type' => 'class',
    'classname' => 'BGREWRITEAOF',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BGREWRITEAOF',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BGSAVE' => 
  array (
    'type' => 'class',
    'classname' => 'BGSAVE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BGSAVE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BITCOUNT' => 
  array (
    'type' => 'class',
    'classname' => 'BITCOUNT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BITCOUNT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BITFIELD' => 
  array (
    'type' => 'class',
    'classname' => 'BITFIELD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BITFIELD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BITFIELD_RO' => 
  array (
    'type' => 'class',
    'classname' => 'BITFIELD_RO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BITFIELD_RO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BITOP' => 
  array (
    'type' => 'class',
    'classname' => 'BITOP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BITOP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BITPOS' => 
  array (
    'type' => 'class',
    'classname' => 'BITPOS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BITPOS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BLMOVE' => 
  array (
    'type' => 'class',
    'classname' => 'BLMOVE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BLMOVE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BLMPOP' => 
  array (
    'type' => 'class',
    'classname' => 'BLMPOP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BLMPOP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BLPOP' => 
  array (
    'type' => 'class',
    'classname' => 'BLPOP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BLPOP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BRPOP' => 
  array (
    'type' => 'class',
    'classname' => 'BRPOP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BRPOP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BRPOPLPUSH' => 
  array (
    'type' => 'class',
    'classname' => 'BRPOPLPUSH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BRPOPLPUSH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BZMPOP' => 
  array (
    'type' => 'class',
    'classname' => 'BZMPOP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BZMPOP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BZPOPMAX' => 
  array (
    'type' => 'class',
    'classname' => 'BZPOPMAX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BZPOPMAX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BZPOPMIN' => 
  array (
    'type' => 'class',
    'classname' => 'BZPOPMIN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BZPOPMIN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BloomFilter\\BFADD' => 
  array (
    'type' => 'class',
    'classname' => 'BFADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\BloomFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BloomFilter\\BFADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BloomFilter\\BFEXISTS' => 
  array (
    'type' => 'class',
    'classname' => 'BFEXISTS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\BloomFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BloomFilter\\BFEXISTS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BloomFilter\\BFINFO' => 
  array (
    'type' => 'class',
    'classname' => 'BFINFO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\BloomFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BloomFilter\\BFINFO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BloomFilter\\BFINSERT' => 
  array (
    'type' => 'class',
    'classname' => 'BFINSERT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\BloomFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BloomFilter\\BFINSERT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BloomFilter\\BFLOADCHUNK' => 
  array (
    'type' => 'class',
    'classname' => 'BFLOADCHUNK',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\BloomFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BloomFilter\\BFLOADCHUNK',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BloomFilter\\BFMADD' => 
  array (
    'type' => 'class',
    'classname' => 'BFMADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\BloomFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BloomFilter\\BFMADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BloomFilter\\BFMEXISTS' => 
  array (
    'type' => 'class',
    'classname' => 'BFMEXISTS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\BloomFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BloomFilter\\BFMEXISTS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BloomFilter\\BFRESERVE' => 
  array (
    'type' => 'class',
    'classname' => 'BFRESERVE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\BloomFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BloomFilter\\BFRESERVE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\BloomFilter\\BFSCANDUMP' => 
  array (
    'type' => 'class',
    'classname' => 'BFSCANDUMP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\BloomFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\BloomFilter\\BFSCANDUMP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CLIENT' => 
  array (
    'type' => 'class',
    'classname' => 'CLIENT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CLIENT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CLUSTER' => 
  array (
    'type' => 'class',
    'classname' => 'CLUSTER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CLUSTER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\COMMAND' => 
  array (
    'type' => 'class',
    'classname' => 'COMMAND',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\COMMAND',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CONFIG' => 
  array (
    'type' => 'class',
    'classname' => 'CONFIG',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CONFIG',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\COPY' => 
  array (
    'type' => 'class',
    'classname' => 'COPY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\COPY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Container\\ACL' => 
  array (
    'type' => 'class',
    'classname' => 'ACL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Container',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Container\\ACL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Container\\AbstractContainer' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractContainer',
    'isabstract' => true,
    'namespace' => 'Predis\\Command\\Redis\\Container',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Container\\AbstractContainer',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Redis\\Container\\ContainerInterface',
    ),
  ),
  'Predis\\Command\\Redis\\Container\\CLUSTER' => 
  array (
    'type' => 'class',
    'classname' => 'CLUSTER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Container',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Container\\CLUSTER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Container\\ContainerFactory' => 
  array (
    'type' => 'class',
    'classname' => 'ContainerFactory',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Container',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Container\\ContainerFactory',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Container\\FunctionContainer' => 
  array (
    'type' => 'class',
    'classname' => 'FunctionContainer',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Container',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Container\\FunctionContainer',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Container\\Json\\JSONDEBUG' => 
  array (
    'type' => 'class',
    'classname' => 'JSONDEBUG',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Container\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Container\\Json\\JSONDEBUG',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Container\\Search\\FTCONFIG' => 
  array (
    'type' => 'class',
    'classname' => 'FTCONFIG',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Container\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Container\\Search\\FTCONFIG',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Container\\Search\\FTCURSOR' => 
  array (
    'type' => 'class',
    'classname' => 'FTCURSOR',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Container\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Container\\Search\\FTCURSOR',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CountMinSketch\\CMSINCRBY' => 
  array (
    'type' => 'class',
    'classname' => 'CMSINCRBY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CountMinSketch',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CountMinSketch\\CMSINCRBY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CountMinSketch\\CMSINFO' => 
  array (
    'type' => 'class',
    'classname' => 'CMSINFO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CountMinSketch',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CountMinSketch\\CMSINFO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CountMinSketch\\CMSINITBYDIM' => 
  array (
    'type' => 'class',
    'classname' => 'CMSINITBYDIM',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CountMinSketch',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CountMinSketch\\CMSINITBYDIM',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CountMinSketch\\CMSINITBYPROB' => 
  array (
    'type' => 'class',
    'classname' => 'CMSINITBYPROB',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CountMinSketch',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CountMinSketch\\CMSINITBYPROB',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CountMinSketch\\CMSMERGE' => 
  array (
    'type' => 'class',
    'classname' => 'CMSMERGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CountMinSketch',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CountMinSketch\\CMSMERGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CountMinSketch\\CMSQUERY' => 
  array (
    'type' => 'class',
    'classname' => 'CMSQUERY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CountMinSketch',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CountMinSketch\\CMSQUERY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFADD' => 
  array (
    'type' => 'class',
    'classname' => 'CFADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFADDNX' => 
  array (
    'type' => 'class',
    'classname' => 'CFADDNX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFADDNX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFCOUNT' => 
  array (
    'type' => 'class',
    'classname' => 'CFCOUNT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFCOUNT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFDEL' => 
  array (
    'type' => 'class',
    'classname' => 'CFDEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFDEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFEXISTS' => 
  array (
    'type' => 'class',
    'classname' => 'CFEXISTS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFEXISTS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFINFO' => 
  array (
    'type' => 'class',
    'classname' => 'CFINFO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFINFO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFINSERT' => 
  array (
    'type' => 'class',
    'classname' => 'CFINSERT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFINSERT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFINSERTNX' => 
  array (
    'type' => 'class',
    'classname' => 'CFINSERTNX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFINSERTNX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFLOADCHUNK' => 
  array (
    'type' => 'class',
    'classname' => 'CFLOADCHUNK',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFLOADCHUNK',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFMEXISTS' => 
  array (
    'type' => 'class',
    'classname' => 'CFMEXISTS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFMEXISTS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFRESERVE' => 
  array (
    'type' => 'class',
    'classname' => 'CFRESERVE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFRESERVE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\CuckooFilter\\CFSCANDUMP' => 
  array (
    'type' => 'class',
    'classname' => 'CFSCANDUMP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\CuckooFilter',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\CuckooFilter\\CFSCANDUMP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\DBSIZE' => 
  array (
    'type' => 'class',
    'classname' => 'DBSIZE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\DBSIZE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\DECR' => 
  array (
    'type' => 'class',
    'classname' => 'DECR',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\DECR',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\DECRBY' => 
  array (
    'type' => 'class',
    'classname' => 'DECRBY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\DECRBY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\DEL' => 
  array (
    'type' => 'class',
    'classname' => 'DEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\DEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\DISCARD' => 
  array (
    'type' => 'class',
    'classname' => 'DISCARD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\DISCARD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\DUMP' => 
  array (
    'type' => 'class',
    'classname' => 'DUMP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\DUMP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ECHO_' => 
  array (
    'type' => 'class',
    'classname' => 'ECHO_',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ECHO_',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\EVALSHA' => 
  array (
    'type' => 'class',
    'classname' => 'EVALSHA',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\EVALSHA',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\EVALSHA_RO' => 
  array (
    'type' => 'class',
    'classname' => 'EVALSHA_RO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\EVALSHA_RO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\EVAL_' => 
  array (
    'type' => 'class',
    'classname' => 'EVAL_',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\EVAL_',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\EVAL_RO' => 
  array (
    'type' => 'class',
    'classname' => 'EVAL_RO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\EVAL_RO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\EXEC' => 
  array (
    'type' => 'class',
    'classname' => 'EXEC',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\EXEC',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\EXISTS' => 
  array (
    'type' => 'class',
    'classname' => 'EXISTS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\EXISTS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\EXPIRE' => 
  array (
    'type' => 'class',
    'classname' => 'EXPIRE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\EXPIRE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\EXPIREAT' => 
  array (
    'type' => 'class',
    'classname' => 'EXPIREAT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\EXPIREAT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\EXPIRETIME' => 
  array (
    'type' => 'class',
    'classname' => 'EXPIRETIME',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\EXPIRETIME',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\FAILOVER' => 
  array (
    'type' => 'class',
    'classname' => 'FAILOVER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\FAILOVER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\FCALL' => 
  array (
    'type' => 'class',
    'classname' => 'FCALL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\FCALL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\FCALL_RO' => 
  array (
    'type' => 'class',
    'classname' => 'FCALL_RO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\FCALL_RO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\FLUSHALL' => 
  array (
    'type' => 'class',
    'classname' => 'FLUSHALL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\FLUSHALL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\FLUSHDB' => 
  array (
    'type' => 'class',
    'classname' => 'FLUSHDB',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\FLUSHDB',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\FUNCTIONS' => 
  array (
    'type' => 'class',
    'classname' => 'FUNCTIONS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\FUNCTIONS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GEOADD' => 
  array (
    'type' => 'class',
    'classname' => 'GEOADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GEOADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GEODIST' => 
  array (
    'type' => 'class',
    'classname' => 'GEODIST',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GEODIST',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GEOHASH' => 
  array (
    'type' => 'class',
    'classname' => 'GEOHASH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GEOHASH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GEOPOS' => 
  array (
    'type' => 'class',
    'classname' => 'GEOPOS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GEOPOS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GEORADIUS' => 
  array (
    'type' => 'class',
    'classname' => 'GEORADIUS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GEORADIUS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GEORADIUSBYMEMBER' => 
  array (
    'type' => 'class',
    'classname' => 'GEORADIUSBYMEMBER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GEORADIUSBYMEMBER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GEOSEARCH' => 
  array (
    'type' => 'class',
    'classname' => 'GEOSEARCH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GEOSEARCH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GEOSEARCHSTORE' => 
  array (
    'type' => 'class',
    'classname' => 'GEOSEARCHSTORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GEOSEARCHSTORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GET' => 
  array (
    'type' => 'class',
    'classname' => 'GET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GETBIT' => 
  array (
    'type' => 'class',
    'classname' => 'GETBIT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GETBIT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GETDEL' => 
  array (
    'type' => 'class',
    'classname' => 'GETDEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GETDEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GETEX' => 
  array (
    'type' => 'class',
    'classname' => 'GETEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GETEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GETRANGE' => 
  array (
    'type' => 'class',
    'classname' => 'GETRANGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GETRANGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\GETSET' => 
  array (
    'type' => 'class',
    'classname' => 'GETSET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\GETSET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HDEL' => 
  array (
    'type' => 'class',
    'classname' => 'HDEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HDEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HEXISTS' => 
  array (
    'type' => 'class',
    'classname' => 'HEXISTS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HEXISTS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HEXPIRE' => 
  array (
    'type' => 'class',
    'classname' => 'HEXPIRE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HEXPIRE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HEXPIREAT' => 
  array (
    'type' => 'class',
    'classname' => 'HEXPIREAT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HEXPIREAT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HEXPIRETIME' => 
  array (
    'type' => 'class',
    'classname' => 'HEXPIRETIME',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HEXPIRETIME',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HGET' => 
  array (
    'type' => 'class',
    'classname' => 'HGET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HGET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HGETALL' => 
  array (
    'type' => 'class',
    'classname' => 'HGETALL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HGETALL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HGETDEL' => 
  array (
    'type' => 'class',
    'classname' => 'HGETDEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HGETDEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HGETEX' => 
  array (
    'type' => 'class',
    'classname' => 'HGETEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HGETEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HINCRBY' => 
  array (
    'type' => 'class',
    'classname' => 'HINCRBY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HINCRBY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HINCRBYFLOAT' => 
  array (
    'type' => 'class',
    'classname' => 'HINCRBYFLOAT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HINCRBYFLOAT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HKEYS' => 
  array (
    'type' => 'class',
    'classname' => 'HKEYS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HKEYS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HLEN' => 
  array (
    'type' => 'class',
    'classname' => 'HLEN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HLEN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HMGET' => 
  array (
    'type' => 'class',
    'classname' => 'HMGET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HMGET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HMSET' => 
  array (
    'type' => 'class',
    'classname' => 'HMSET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HMSET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HPERSIST' => 
  array (
    'type' => 'class',
    'classname' => 'HPERSIST',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HPERSIST',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HPEXPIRE' => 
  array (
    'type' => 'class',
    'classname' => 'HPEXPIRE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HPEXPIRE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HPEXPIREAT' => 
  array (
    'type' => 'class',
    'classname' => 'HPEXPIREAT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HPEXPIREAT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HPEXPIRETIME' => 
  array (
    'type' => 'class',
    'classname' => 'HPEXPIRETIME',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HPEXPIRETIME',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HPTTL' => 
  array (
    'type' => 'class',
    'classname' => 'HPTTL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HPTTL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HRANDFIELD' => 
  array (
    'type' => 'class',
    'classname' => 'HRANDFIELD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HRANDFIELD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HSCAN' => 
  array (
    'type' => 'class',
    'classname' => 'HSCAN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HSCAN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HSET' => 
  array (
    'type' => 'class',
    'classname' => 'HSET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HSET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HSETEX' => 
  array (
    'type' => 'class',
    'classname' => 'HSETEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HSETEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HSETNX' => 
  array (
    'type' => 'class',
    'classname' => 'HSETNX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HSETNX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HSTRLEN' => 
  array (
    'type' => 'class',
    'classname' => 'HSTRLEN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HSTRLEN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HTTL' => 
  array (
    'type' => 'class',
    'classname' => 'HTTL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HTTL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\HVALS' => 
  array (
    'type' => 'class',
    'classname' => 'HVALS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\HVALS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\INCR' => 
  array (
    'type' => 'class',
    'classname' => 'INCR',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\INCR',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\INCRBY' => 
  array (
    'type' => 'class',
    'classname' => 'INCRBY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\INCRBY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\INCRBYFLOAT' => 
  array (
    'type' => 'class',
    'classname' => 'INCRBYFLOAT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\INCRBYFLOAT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\INFO' => 
  array (
    'type' => 'class',
    'classname' => 'INFO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\INFO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONARRAPPEND' => 
  array (
    'type' => 'class',
    'classname' => 'JSONARRAPPEND',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONARRAPPEND',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONARRINDEX' => 
  array (
    'type' => 'class',
    'classname' => 'JSONARRINDEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONARRINDEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONARRINSERT' => 
  array (
    'type' => 'class',
    'classname' => 'JSONARRINSERT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONARRINSERT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONARRLEN' => 
  array (
    'type' => 'class',
    'classname' => 'JSONARRLEN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONARRLEN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONARRPOP' => 
  array (
    'type' => 'class',
    'classname' => 'JSONARRPOP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONARRPOP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONARRTRIM' => 
  array (
    'type' => 'class',
    'classname' => 'JSONARRTRIM',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONARRTRIM',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONCLEAR' => 
  array (
    'type' => 'class',
    'classname' => 'JSONCLEAR',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONCLEAR',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONDEBUG' => 
  array (
    'type' => 'class',
    'classname' => 'JSONDEBUG',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONDEBUG',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONDEL' => 
  array (
    'type' => 'class',
    'classname' => 'JSONDEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONDEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONFORGET' => 
  array (
    'type' => 'class',
    'classname' => 'JSONFORGET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONFORGET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONGET' => 
  array (
    'type' => 'class',
    'classname' => 'JSONGET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONGET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONMERGE' => 
  array (
    'type' => 'class',
    'classname' => 'JSONMERGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONMERGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONMGET' => 
  array (
    'type' => 'class',
    'classname' => 'JSONMGET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONMGET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONMSET' => 
  array (
    'type' => 'class',
    'classname' => 'JSONMSET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONMSET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONNUMINCRBY' => 
  array (
    'type' => 'class',
    'classname' => 'JSONNUMINCRBY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONNUMINCRBY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONOBJKEYS' => 
  array (
    'type' => 'class',
    'classname' => 'JSONOBJKEYS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONOBJKEYS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONOBJLEN' => 
  array (
    'type' => 'class',
    'classname' => 'JSONOBJLEN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONOBJLEN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONRESP' => 
  array (
    'type' => 'class',
    'classname' => 'JSONRESP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONRESP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONSET' => 
  array (
    'type' => 'class',
    'classname' => 'JSONSET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONSET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONSTRAPPEND' => 
  array (
    'type' => 'class',
    'classname' => 'JSONSTRAPPEND',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONSTRAPPEND',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONSTRLEN' => 
  array (
    'type' => 'class',
    'classname' => 'JSONSTRLEN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONSTRLEN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONTOGGLE' => 
  array (
    'type' => 'class',
    'classname' => 'JSONTOGGLE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONTOGGLE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Json\\JSONTYPE' => 
  array (
    'type' => 'class',
    'classname' => 'JSONTYPE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Json',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Json\\JSONTYPE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\KEYS' => 
  array (
    'type' => 'class',
    'classname' => 'KEYS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\KEYS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LASTSAVE' => 
  array (
    'type' => 'class',
    'classname' => 'LASTSAVE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LASTSAVE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LCS' => 
  array (
    'type' => 'class',
    'classname' => 'LCS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LCS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LINDEX' => 
  array (
    'type' => 'class',
    'classname' => 'LINDEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LINDEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LINSERT' => 
  array (
    'type' => 'class',
    'classname' => 'LINSERT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LINSERT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LLEN' => 
  array (
    'type' => 'class',
    'classname' => 'LLEN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LLEN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LMOVE' => 
  array (
    'type' => 'class',
    'classname' => 'LMOVE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LMOVE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LMPOP' => 
  array (
    'type' => 'class',
    'classname' => 'LMPOP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LMPOP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LPOP' => 
  array (
    'type' => 'class',
    'classname' => 'LPOP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LPOP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LPUSH' => 
  array (
    'type' => 'class',
    'classname' => 'LPUSH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LPUSH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LPUSHX' => 
  array (
    'type' => 'class',
    'classname' => 'LPUSHX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LPUSHX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LRANGE' => 
  array (
    'type' => 'class',
    'classname' => 'LRANGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LRANGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LREM' => 
  array (
    'type' => 'class',
    'classname' => 'LREM',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LREM',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LSET' => 
  array (
    'type' => 'class',
    'classname' => 'LSET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LSET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\LTRIM' => 
  array (
    'type' => 'class',
    'classname' => 'LTRIM',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\LTRIM',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\MGET' => 
  array (
    'type' => 'class',
    'classname' => 'MGET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\MGET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\MIGRATE' => 
  array (
    'type' => 'class',
    'classname' => 'MIGRATE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\MIGRATE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\MONITOR' => 
  array (
    'type' => 'class',
    'classname' => 'MONITOR',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\MONITOR',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\MOVE' => 
  array (
    'type' => 'class',
    'classname' => 'MOVE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\MOVE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\MSET' => 
  array (
    'type' => 'class',
    'classname' => 'MSET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\MSET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\MSETNX' => 
  array (
    'type' => 'class',
    'classname' => 'MSETNX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\MSETNX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\MULTI' => 
  array (
    'type' => 'class',
    'classname' => 'MULTI',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\MULTI',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\OBJECT_' => 
  array (
    'type' => 'class',
    'classname' => 'OBJECT_',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\OBJECT_',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PERSIST' => 
  array (
    'type' => 'class',
    'classname' => 'PERSIST',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PERSIST',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PEXPIRE' => 
  array (
    'type' => 'class',
    'classname' => 'PEXPIRE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PEXPIRE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PEXPIREAT' => 
  array (
    'type' => 'class',
    'classname' => 'PEXPIREAT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PEXPIREAT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PEXPIRETIME' => 
  array (
    'type' => 'class',
    'classname' => 'PEXPIRETIME',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PEXPIRETIME',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PFADD' => 
  array (
    'type' => 'class',
    'classname' => 'PFADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PFADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PFCOUNT' => 
  array (
    'type' => 'class',
    'classname' => 'PFCOUNT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PFCOUNT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PFMERGE' => 
  array (
    'type' => 'class',
    'classname' => 'PFMERGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PFMERGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PING' => 
  array (
    'type' => 'class',
    'classname' => 'PING',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PING',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PSETEX' => 
  array (
    'type' => 'class',
    'classname' => 'PSETEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PSETEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PSUBSCRIBE' => 
  array (
    'type' => 'class',
    'classname' => 'PSUBSCRIBE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PSUBSCRIBE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PTTL' => 
  array (
    'type' => 'class',
    'classname' => 'PTTL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PTTL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PUBLISH' => 
  array (
    'type' => 'class',
    'classname' => 'PUBLISH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PUBLISH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PUBSUB' => 
  array (
    'type' => 'class',
    'classname' => 'PUBSUB',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PUBSUB',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\PUNSUBSCRIBE' => 
  array (
    'type' => 'class',
    'classname' => 'PUNSUBSCRIBE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\PUNSUBSCRIBE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\QUIT' => 
  array (
    'type' => 'class',
    'classname' => 'QUIT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\QUIT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\RANDOMKEY' => 
  array (
    'type' => 'class',
    'classname' => 'RANDOMKEY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\RANDOMKEY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\RENAME' => 
  array (
    'type' => 'class',
    'classname' => 'RENAME',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\RENAME',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\RENAMENX' => 
  array (
    'type' => 'class',
    'classname' => 'RENAMENX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\RENAMENX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\RESTORE' => 
  array (
    'type' => 'class',
    'classname' => 'RESTORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\RESTORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\RPOP' => 
  array (
    'type' => 'class',
    'classname' => 'RPOP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\RPOP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\RPOPLPUSH' => 
  array (
    'type' => 'class',
    'classname' => 'RPOPLPUSH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\RPOPLPUSH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\RPUSH' => 
  array (
    'type' => 'class',
    'classname' => 'RPUSH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\RPUSH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\RPUSHX' => 
  array (
    'type' => 'class',
    'classname' => 'RPUSHX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\RPUSHX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SADD' => 
  array (
    'type' => 'class',
    'classname' => 'SADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SAVE' => 
  array (
    'type' => 'class',
    'classname' => 'SAVE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SAVE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SCAN' => 
  array (
    'type' => 'class',
    'classname' => 'SCAN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SCAN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SCARD' => 
  array (
    'type' => 'class',
    'classname' => 'SCARD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SCARD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SCRIPT' => 
  array (
    'type' => 'class',
    'classname' => 'SCRIPT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SCRIPT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SDIFF' => 
  array (
    'type' => 'class',
    'classname' => 'SDIFF',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SDIFF',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SDIFFSTORE' => 
  array (
    'type' => 'class',
    'classname' => 'SDIFFSTORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SDIFFSTORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SELECT' => 
  array (
    'type' => 'class',
    'classname' => 'SELECT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SELECT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SENTINEL' => 
  array (
    'type' => 'class',
    'classname' => 'SENTINEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SENTINEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SET' => 
  array (
    'type' => 'class',
    'classname' => 'SET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SETBIT' => 
  array (
    'type' => 'class',
    'classname' => 'SETBIT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SETBIT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SETEX' => 
  array (
    'type' => 'class',
    'classname' => 'SETEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SETEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SETNX' => 
  array (
    'type' => 'class',
    'classname' => 'SETNX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SETNX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SETRANGE' => 
  array (
    'type' => 'class',
    'classname' => 'SETRANGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SETRANGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SHUTDOWN' => 
  array (
    'type' => 'class',
    'classname' => 'SHUTDOWN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SHUTDOWN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SINTER' => 
  array (
    'type' => 'class',
    'classname' => 'SINTER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SINTER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SINTERCARD' => 
  array (
    'type' => 'class',
    'classname' => 'SINTERCARD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SINTERCARD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SINTERSTORE' => 
  array (
    'type' => 'class',
    'classname' => 'SINTERSTORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SINTERSTORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SISMEMBER' => 
  array (
    'type' => 'class',
    'classname' => 'SISMEMBER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SISMEMBER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SLAVEOF' => 
  array (
    'type' => 'class',
    'classname' => 'SLAVEOF',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SLAVEOF',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SLOWLOG' => 
  array (
    'type' => 'class',
    'classname' => 'SLOWLOG',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SLOWLOG',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SMEMBERS' => 
  array (
    'type' => 'class',
    'classname' => 'SMEMBERS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SMEMBERS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SMISMEMBER' => 
  array (
    'type' => 'class',
    'classname' => 'SMISMEMBER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SMISMEMBER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SMOVE' => 
  array (
    'type' => 'class',
    'classname' => 'SMOVE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SMOVE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SORT' => 
  array (
    'type' => 'class',
    'classname' => 'SORT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SORT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SORT_RO' => 
  array (
    'type' => 'class',
    'classname' => 'SORT_RO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SORT_RO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SPOP' => 
  array (
    'type' => 'class',
    'classname' => 'SPOP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SPOP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SRANDMEMBER' => 
  array (
    'type' => 'class',
    'classname' => 'SRANDMEMBER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SRANDMEMBER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SREM' => 
  array (
    'type' => 'class',
    'classname' => 'SREM',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SREM',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SSCAN' => 
  array (
    'type' => 'class',
    'classname' => 'SSCAN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SSCAN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\STRLEN' => 
  array (
    'type' => 'class',
    'classname' => 'STRLEN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\STRLEN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SUBSCRIBE' => 
  array (
    'type' => 'class',
    'classname' => 'SUBSCRIBE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SUBSCRIBE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SUBSTR' => 
  array (
    'type' => 'class',
    'classname' => 'SUBSTR',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SUBSTR',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SUNION' => 
  array (
    'type' => 'class',
    'classname' => 'SUNION',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SUNION',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\SUNIONSTORE' => 
  array (
    'type' => 'class',
    'classname' => 'SUNIONSTORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\SUNIONSTORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTAGGREGATE' => 
  array (
    'type' => 'class',
    'classname' => 'FTAGGREGATE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTAGGREGATE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTALIASADD' => 
  array (
    'type' => 'class',
    'classname' => 'FTALIASADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTALIASADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTALIASDEL' => 
  array (
    'type' => 'class',
    'classname' => 'FTALIASDEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTALIASDEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTALIASUPDATE' => 
  array (
    'type' => 'class',
    'classname' => 'FTALIASUPDATE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTALIASUPDATE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTALTER' => 
  array (
    'type' => 'class',
    'classname' => 'FTALTER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTALTER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTCONFIG' => 
  array (
    'type' => 'class',
    'classname' => 'FTCONFIG',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTCONFIG',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTCREATE' => 
  array (
    'type' => 'class',
    'classname' => 'FTCREATE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTCREATE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTCURSOR' => 
  array (
    'type' => 'class',
    'classname' => 'FTCURSOR',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTCURSOR',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTDICTADD' => 
  array (
    'type' => 'class',
    'classname' => 'FTDICTADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTDICTADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTDICTDEL' => 
  array (
    'type' => 'class',
    'classname' => 'FTDICTDEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTDICTDEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTDICTDUMP' => 
  array (
    'type' => 'class',
    'classname' => 'FTDICTDUMP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTDICTDUMP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTDROPINDEX' => 
  array (
    'type' => 'class',
    'classname' => 'FTDROPINDEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTDROPINDEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTEXPLAIN' => 
  array (
    'type' => 'class',
    'classname' => 'FTEXPLAIN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTEXPLAIN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTINFO' => 
  array (
    'type' => 'class',
    'classname' => 'FTINFO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTINFO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTPROFILE' => 
  array (
    'type' => 'class',
    'classname' => 'FTPROFILE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTPROFILE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTSEARCH' => 
  array (
    'type' => 'class',
    'classname' => 'FTSEARCH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTSEARCH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTSPELLCHECK' => 
  array (
    'type' => 'class',
    'classname' => 'FTSPELLCHECK',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTSPELLCHECK',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTSUGADD' => 
  array (
    'type' => 'class',
    'classname' => 'FTSUGADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTSUGADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTSUGDEL' => 
  array (
    'type' => 'class',
    'classname' => 'FTSUGDEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTSUGDEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTSUGGET' => 
  array (
    'type' => 'class',
    'classname' => 'FTSUGGET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTSUGGET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTSUGLEN' => 
  array (
    'type' => 'class',
    'classname' => 'FTSUGLEN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTSUGLEN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTSYNDUMP' => 
  array (
    'type' => 'class',
    'classname' => 'FTSYNDUMP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTSYNDUMP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTSYNUPDATE' => 
  array (
    'type' => 'class',
    'classname' => 'FTSYNUPDATE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTSYNUPDATE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FTTAGVALS' => 
  array (
    'type' => 'class',
    'classname' => 'FTTAGVALS',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FTTAGVALS',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\Search\\FT_LIST' => 
  array (
    'type' => 'class',
    'classname' => 'FT_LIST',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\Search',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Search\\FT_LIST',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTADD' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTBYRANK' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTBYRANK',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTBYRANK',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTBYREVRANK' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTBYREVRANK',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTBYREVRANK',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTCDF' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTCDF',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTCDF',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTCREATE' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTCREATE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTCREATE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTINFO' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTINFO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTINFO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTMAX' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTMAX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTMAX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTMERGE' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTMERGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTMERGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTMIN' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTMIN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTMIN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTQUANTILE' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTQUANTILE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTQUANTILE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTRANK' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTRANK',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTRANK',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTRESET' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTRESET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTRESET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTREVRANK' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTREVRANK',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTREVRANK',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TDigest\\TDIGESTTRIMMED_MEAN' => 
  array (
    'type' => 'class',
    'classname' => 'TDIGESTTRIMMED_MEAN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TDigest',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TDigest\\TDIGESTTRIMMED_MEAN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TIME' => 
  array (
    'type' => 'class',
    'classname' => 'TIME',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TIME',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TOUCH' => 
  array (
    'type' => 'class',
    'classname' => 'TOUCH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TOUCH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TTL' => 
  array (
    'type' => 'class',
    'classname' => 'TTL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TTL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TYPE' => 
  array (
    'type' => 'class',
    'classname' => 'TYPE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TYPE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSADD' => 
  array (
    'type' => 'class',
    'classname' => 'TSADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSALTER' => 
  array (
    'type' => 'class',
    'classname' => 'TSALTER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSALTER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSCREATE' => 
  array (
    'type' => 'class',
    'classname' => 'TSCREATE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSCREATE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSCREATERULE' => 
  array (
    'type' => 'class',
    'classname' => 'TSCREATERULE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSCREATERULE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSDECRBY' => 
  array (
    'type' => 'class',
    'classname' => 'TSDECRBY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSDECRBY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSDEL' => 
  array (
    'type' => 'class',
    'classname' => 'TSDEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSDEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSDELETERULE' => 
  array (
    'type' => 'class',
    'classname' => 'TSDELETERULE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSDELETERULE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSGET' => 
  array (
    'type' => 'class',
    'classname' => 'TSGET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSGET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSINCRBY' => 
  array (
    'type' => 'class',
    'classname' => 'TSINCRBY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSINCRBY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSINFO' => 
  array (
    'type' => 'class',
    'classname' => 'TSINFO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSINFO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSMADD' => 
  array (
    'type' => 'class',
    'classname' => 'TSMADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSMADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSMGET' => 
  array (
    'type' => 'class',
    'classname' => 'TSMGET',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSMGET',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSMRANGE' => 
  array (
    'type' => 'class',
    'classname' => 'TSMRANGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSMRANGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSMREVRANGE' => 
  array (
    'type' => 'class',
    'classname' => 'TSMREVRANGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSMREVRANGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSQUERYINDEX' => 
  array (
    'type' => 'class',
    'classname' => 'TSQUERYINDEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSQUERYINDEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSRANGE' => 
  array (
    'type' => 'class',
    'classname' => 'TSRANGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSRANGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TimeSeries\\TSREVRANGE' => 
  array (
    'type' => 'class',
    'classname' => 'TSREVRANGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TimeSeries',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TimeSeries\\TSREVRANGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TopK\\TOPKADD' => 
  array (
    'type' => 'class',
    'classname' => 'TOPKADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TopK',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TopK\\TOPKADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TopK\\TOPKINCRBY' => 
  array (
    'type' => 'class',
    'classname' => 'TOPKINCRBY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TopK',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TopK\\TOPKINCRBY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TopK\\TOPKINFO' => 
  array (
    'type' => 'class',
    'classname' => 'TOPKINFO',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TopK',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TopK\\TOPKINFO',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TopK\\TOPKLIST' => 
  array (
    'type' => 'class',
    'classname' => 'TOPKLIST',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TopK',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TopK\\TOPKLIST',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TopK\\TOPKQUERY' => 
  array (
    'type' => 'class',
    'classname' => 'TOPKQUERY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TopK',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TopK\\TOPKQUERY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\TopK\\TOPKRESERVE' => 
  array (
    'type' => 'class',
    'classname' => 'TOPKRESERVE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis\\TopK',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\TopK\\TOPKRESERVE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\UNSUBSCRIBE' => 
  array (
    'type' => 'class',
    'classname' => 'UNSUBSCRIBE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\UNSUBSCRIBE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\UNWATCH' => 
  array (
    'type' => 'class',
    'classname' => 'UNWATCH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\UNWATCH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\WAITAOF' => 
  array (
    'type' => 'class',
    'classname' => 'WAITAOF',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\WAITAOF',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\WATCH' => 
  array (
    'type' => 'class',
    'classname' => 'WATCH',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\WATCH',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\XADD' => 
  array (
    'type' => 'class',
    'classname' => 'XADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\XADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\XDEL' => 
  array (
    'type' => 'class',
    'classname' => 'XDEL',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\XDEL',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\XLEN' => 
  array (
    'type' => 'class',
    'classname' => 'XLEN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\XLEN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\XRANGE' => 
  array (
    'type' => 'class',
    'classname' => 'XRANGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\XRANGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\XREAD' => 
  array (
    'type' => 'class',
    'classname' => 'XREAD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\XREAD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\XREVRANGE' => 
  array (
    'type' => 'class',
    'classname' => 'XREVRANGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\XREVRANGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\XTRIM' => 
  array (
    'type' => 'class',
    'classname' => 'XTRIM',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\XTRIM',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZADD' => 
  array (
    'type' => 'class',
    'classname' => 'ZADD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZADD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZCARD' => 
  array (
    'type' => 'class',
    'classname' => 'ZCARD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZCARD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZCOUNT' => 
  array (
    'type' => 'class',
    'classname' => 'ZCOUNT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZCOUNT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZDIFF' => 
  array (
    'type' => 'class',
    'classname' => 'ZDIFF',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZDIFF',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZDIFFSTORE' => 
  array (
    'type' => 'class',
    'classname' => 'ZDIFFSTORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZDIFFSTORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZINCRBY' => 
  array (
    'type' => 'class',
    'classname' => 'ZINCRBY',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZINCRBY',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZINTER' => 
  array (
    'type' => 'class',
    'classname' => 'ZINTER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZINTER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZINTERCARD' => 
  array (
    'type' => 'class',
    'classname' => 'ZINTERCARD',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZINTERCARD',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZINTERSTORE' => 
  array (
    'type' => 'class',
    'classname' => 'ZINTERSTORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZINTERSTORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZLEXCOUNT' => 
  array (
    'type' => 'class',
    'classname' => 'ZLEXCOUNT',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZLEXCOUNT',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZMPOP' => 
  array (
    'type' => 'class',
    'classname' => 'ZMPOP',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZMPOP',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZMSCORE' => 
  array (
    'type' => 'class',
    'classname' => 'ZMSCORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZMSCORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZPOPMAX' => 
  array (
    'type' => 'class',
    'classname' => 'ZPOPMAX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZPOPMAX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZPOPMIN' => 
  array (
    'type' => 'class',
    'classname' => 'ZPOPMIN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZPOPMIN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZRANDMEMBER' => 
  array (
    'type' => 'class',
    'classname' => 'ZRANDMEMBER',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZRANDMEMBER',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZRANGE' => 
  array (
    'type' => 'class',
    'classname' => 'ZRANGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZRANGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZRANGEBYLEX' => 
  array (
    'type' => 'class',
    'classname' => 'ZRANGEBYLEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZRANGEBYLEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZRANGEBYSCORE' => 
  array (
    'type' => 'class',
    'classname' => 'ZRANGEBYSCORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZRANGEBYSCORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZRANGESTORE' => 
  array (
    'type' => 'class',
    'classname' => 'ZRANGESTORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZRANGESTORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZRANK' => 
  array (
    'type' => 'class',
    'classname' => 'ZRANK',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZRANK',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZREM' => 
  array (
    'type' => 'class',
    'classname' => 'ZREM',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZREM',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZREMRANGEBYLEX' => 
  array (
    'type' => 'class',
    'classname' => 'ZREMRANGEBYLEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZREMRANGEBYLEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZREMRANGEBYRANK' => 
  array (
    'type' => 'class',
    'classname' => 'ZREMRANGEBYRANK',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZREMRANGEBYRANK',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZREMRANGEBYSCORE' => 
  array (
    'type' => 'class',
    'classname' => 'ZREMRANGEBYSCORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZREMRANGEBYSCORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZREVRANGE' => 
  array (
    'type' => 'class',
    'classname' => 'ZREVRANGE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZREVRANGE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZREVRANGEBYLEX' => 
  array (
    'type' => 'class',
    'classname' => 'ZREVRANGEBYLEX',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZREVRANGEBYLEX',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZREVRANGEBYSCORE' => 
  array (
    'type' => 'class',
    'classname' => 'ZREVRANGEBYSCORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZREVRANGEBYSCORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZREVRANK' => 
  array (
    'type' => 'class',
    'classname' => 'ZREVRANK',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZREVRANK',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZSCAN' => 
  array (
    'type' => 'class',
    'classname' => 'ZSCAN',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZSCAN',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZSCORE' => 
  array (
    'type' => 'class',
    'classname' => 'ZSCORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZSCORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZUNION' => 
  array (
    'type' => 'class',
    'classname' => 'ZUNION',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZUNION',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Redis\\ZUNIONSTORE' => 
  array (
    'type' => 'class',
    'classname' => 'ZUNIONSTORE',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Redis',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Redis\\ZUNIONSTORE',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\RedisFactory' => 
  array (
    'type' => 'class',
    'classname' => 'RedisFactory',
    'isabstract' => false,
    'namespace' => 'Predis\\Command',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\RedisFactory',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\ScriptCommand' => 
  array (
    'type' => 'class',
    'classname' => 'ScriptCommand',
    'isabstract' => true,
    'namespace' => 'Predis\\Command',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\ScriptCommand',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Strategy\\ContainerCommands\\Functions\\DeleteStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'DeleteStrategy',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Strategy\\ContainerCommands\\Functions',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Strategy\\ContainerCommands\\Functions\\DeleteStrategy',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Strategy\\SubcommandStrategyInterface',
    ),
  ),
  'Predis\\Command\\Strategy\\ContainerCommands\\Functions\\DumpStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'DumpStrategy',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Strategy\\ContainerCommands\\Functions',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Strategy\\ContainerCommands\\Functions\\DumpStrategy',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Strategy\\SubcommandStrategyInterface',
    ),
  ),
  'Predis\\Command\\Strategy\\ContainerCommands\\Functions\\FlushStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'FlushStrategy',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Strategy\\ContainerCommands\\Functions',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Strategy\\ContainerCommands\\Functions\\FlushStrategy',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Strategy\\SubcommandStrategyInterface',
    ),
  ),
  'Predis\\Command\\Strategy\\ContainerCommands\\Functions\\KillStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'KillStrategy',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Strategy\\ContainerCommands\\Functions',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Strategy\\ContainerCommands\\Functions\\KillStrategy',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Strategy\\SubcommandStrategyInterface',
    ),
  ),
  'Predis\\Command\\Strategy\\ContainerCommands\\Functions\\ListStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'ListStrategy',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Strategy\\ContainerCommands\\Functions',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Strategy\\ContainerCommands\\Functions\\ListStrategy',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Strategy\\SubcommandStrategyInterface',
    ),
  ),
  'Predis\\Command\\Strategy\\ContainerCommands\\Functions\\LoadStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'LoadStrategy',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Strategy\\ContainerCommands\\Functions',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Strategy\\ContainerCommands\\Functions\\LoadStrategy',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Strategy\\SubcommandStrategyInterface',
    ),
  ),
  'Predis\\Command\\Strategy\\ContainerCommands\\Functions\\RestoreStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'RestoreStrategy',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Strategy\\ContainerCommands\\Functions',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Strategy\\ContainerCommands\\Functions\\RestoreStrategy',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Strategy\\SubcommandStrategyInterface',
    ),
  ),
  'Predis\\Command\\Strategy\\ContainerCommands\\Functions\\StatsStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'StatsStrategy',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Strategy\\ContainerCommands\\Functions',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Strategy\\ContainerCommands\\Functions\\StatsStrategy',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Strategy\\SubcommandStrategyInterface',
    ),
  ),
  'Predis\\Command\\Strategy\\SubcommandStrategyResolver' => 
  array (
    'type' => 'class',
    'classname' => 'SubcommandStrategyResolver',
    'isabstract' => false,
    'namespace' => 'Predis\\Command\\Strategy',
    'extends' => 'MilliCache\\Deps\\Predis\\Command\\Strategy\\SubcommandStrategyResolver',
    'implements' => 
    array (
      0 => 'Predis\\Command\\Strategy\\StrategyResolverInterface',
    ),
  ),
  'Predis\\CommunicationException' => 
  array (
    'type' => 'class',
    'classname' => 'CommunicationException',
    'isabstract' => true,
    'namespace' => 'Predis',
    'extends' => 'MilliCache\\Deps\\Predis\\CommunicationException',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Configuration\\Option\\Aggregate' => 
  array (
    'type' => 'class',
    'classname' => 'Aggregate',
    'isabstract' => false,
    'namespace' => 'Predis\\Configuration\\Option',
    'extends' => 'MilliCache\\Deps\\Predis\\Configuration\\Option\\Aggregate',
    'implements' => 
    array (
      0 => 'Predis\\Configuration\\OptionInterface',
    ),
  ),
  'Predis\\Configuration\\Option\\CRC16' => 
  array (
    'type' => 'class',
    'classname' => 'CRC16',
    'isabstract' => false,
    'namespace' => 'Predis\\Configuration\\Option',
    'extends' => 'MilliCache\\Deps\\Predis\\Configuration\\Option\\CRC16',
    'implements' => 
    array (
      0 => 'Predis\\Configuration\\OptionInterface',
    ),
  ),
  'Predis\\Configuration\\Option\\Cluster' => 
  array (
    'type' => 'class',
    'classname' => 'Cluster',
    'isabstract' => false,
    'namespace' => 'Predis\\Configuration\\Option',
    'extends' => 'MilliCache\\Deps\\Predis\\Configuration\\Option\\Cluster',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Configuration\\Option\\Commands' => 
  array (
    'type' => 'class',
    'classname' => 'Commands',
    'isabstract' => false,
    'namespace' => 'Predis\\Configuration\\Option',
    'extends' => 'MilliCache\\Deps\\Predis\\Configuration\\Option\\Commands',
    'implements' => 
    array (
      0 => 'Predis\\Configuration\\OptionInterface',
    ),
  ),
  'Predis\\Configuration\\Option\\Connections' => 
  array (
    'type' => 'class',
    'classname' => 'Connections',
    'isabstract' => false,
    'namespace' => 'Predis\\Configuration\\Option',
    'extends' => 'MilliCache\\Deps\\Predis\\Configuration\\Option\\Connections',
    'implements' => 
    array (
      0 => 'Predis\\Configuration\\OptionInterface',
    ),
  ),
  'Predis\\Configuration\\Option\\Exceptions' => 
  array (
    'type' => 'class',
    'classname' => 'Exceptions',
    'isabstract' => false,
    'namespace' => 'Predis\\Configuration\\Option',
    'extends' => 'MilliCache\\Deps\\Predis\\Configuration\\Option\\Exceptions',
    'implements' => 
    array (
      0 => 'Predis\\Configuration\\OptionInterface',
    ),
  ),
  'Predis\\Configuration\\Option\\Prefix' => 
  array (
    'type' => 'class',
    'classname' => 'Prefix',
    'isabstract' => false,
    'namespace' => 'Predis\\Configuration\\Option',
    'extends' => 'MilliCache\\Deps\\Predis\\Configuration\\Option\\Prefix',
    'implements' => 
    array (
      0 => 'Predis\\Configuration\\OptionInterface',
    ),
  ),
  'Predis\\Configuration\\Option\\Replication' => 
  array (
    'type' => 'class',
    'classname' => 'Replication',
    'isabstract' => false,
    'namespace' => 'Predis\\Configuration\\Option',
    'extends' => 'MilliCache\\Deps\\Predis\\Configuration\\Option\\Replication',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Configuration\\Options' => 
  array (
    'type' => 'class',
    'classname' => 'Options',
    'isabstract' => false,
    'namespace' => 'Predis\\Configuration',
    'extends' => 'MilliCache\\Deps\\Predis\\Configuration\\Options',
    'implements' => 
    array (
      0 => 'Predis\\Configuration\\OptionsInterface',
    ),
  ),
  'Predis\\Connection\\AbstractConnection' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractConnection',
    'isabstract' => true,
    'namespace' => 'Predis\\Connection',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\AbstractConnection',
    'implements' => 
    array (
      0 => 'Predis\\Connection\\NodeConnectionInterface',
    ),
  ),
  'Predis\\Connection\\Cluster\\PredisCluster' => 
  array (
    'type' => 'class',
    'classname' => 'PredisCluster',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection\\Cluster',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\Cluster\\PredisCluster',
    'implements' => 
    array (
      0 => 'Predis\\Connection\\Cluster\\ClusterInterface',
      1 => 'IteratorAggregate',
      2 => 'Countable',
    ),
  ),
  'Predis\\Connection\\Cluster\\RedisCluster' => 
  array (
    'type' => 'class',
    'classname' => 'RedisCluster',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection\\Cluster',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\Cluster\\RedisCluster',
    'implements' => 
    array (
      0 => 'Predis\\Connection\\Cluster\\ClusterInterface',
      1 => 'IteratorAggregate',
      2 => 'Countable',
    ),
  ),
  'Predis\\Connection\\CompositeStreamConnection' => 
  array (
    'type' => 'class',
    'classname' => 'CompositeStreamConnection',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\CompositeStreamConnection',
    'implements' => 
    array (
      0 => 'Predis\\Connection\\CompositeConnectionInterface',
    ),
  ),
  'Predis\\Connection\\ConnectionException' => 
  array (
    'type' => 'class',
    'classname' => 'ConnectionException',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\ConnectionException',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Connection\\Factory' => 
  array (
    'type' => 'class',
    'classname' => 'Factory',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\Factory',
    'implements' => 
    array (
      0 => 'Predis\\Connection\\FactoryInterface',
    ),
  ),
  'Predis\\Connection\\Parameters' => 
  array (
    'type' => 'class',
    'classname' => 'Parameters',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\Parameters',
    'implements' => 
    array (
      0 => 'Predis\\Connection\\ParametersInterface',
    ),
  ),
  'Predis\\Connection\\PhpiredisSocketConnection' => 
  array (
    'type' => 'class',
    'classname' => 'PhpiredisSocketConnection',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\PhpiredisSocketConnection',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Connection\\PhpiredisStreamConnection' => 
  array (
    'type' => 'class',
    'classname' => 'PhpiredisStreamConnection',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\PhpiredisStreamConnection',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Connection\\RelayConnection' => 
  array (
    'type' => 'class',
    'classname' => 'RelayConnection',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\RelayConnection',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Connection\\Replication\\MasterSlaveReplication' => 
  array (
    'type' => 'class',
    'classname' => 'MasterSlaveReplication',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection\\Replication',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\Replication\\MasterSlaveReplication',
    'implements' => 
    array (
      0 => 'Predis\\Connection\\Replication\\ReplicationInterface',
    ),
  ),
  'Predis\\Connection\\Replication\\SentinelReplication' => 
  array (
    'type' => 'class',
    'classname' => 'SentinelReplication',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection\\Replication',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\Replication\\SentinelReplication',
    'implements' => 
    array (
      0 => 'Predis\\Connection\\Replication\\ReplicationInterface',
    ),
  ),
  'Predis\\Connection\\StreamConnection' => 
  array (
    'type' => 'class',
    'classname' => 'StreamConnection',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\StreamConnection',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Connection\\WebdisConnection' => 
  array (
    'type' => 'class',
    'classname' => 'WebdisConnection',
    'isabstract' => false,
    'namespace' => 'Predis\\Connection',
    'extends' => 'MilliCache\\Deps\\Predis\\Connection\\WebdisConnection',
    'implements' => 
    array (
      0 => 'Predis\\Connection\\NodeConnectionInterface',
    ),
  ),
  'Predis\\Monitor\\Consumer' => 
  array (
    'type' => 'class',
    'classname' => 'Consumer',
    'isabstract' => false,
    'namespace' => 'Predis\\Monitor',
    'extends' => 'MilliCache\\Deps\\Predis\\Monitor\\Consumer',
    'implements' => 
    array (
      0 => 'Iterator',
    ),
  ),
  'Predis\\NotSupportedException' => 
  array (
    'type' => 'class',
    'classname' => 'NotSupportedException',
    'isabstract' => false,
    'namespace' => 'Predis',
    'extends' => 'MilliCache\\Deps\\Predis\\NotSupportedException',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Pipeline\\Atomic' => 
  array (
    'type' => 'class',
    'classname' => 'Atomic',
    'isabstract' => false,
    'namespace' => 'Predis\\Pipeline',
    'extends' => 'MilliCache\\Deps\\Predis\\Pipeline\\Atomic',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Pipeline\\ConnectionErrorProof' => 
  array (
    'type' => 'class',
    'classname' => 'ConnectionErrorProof',
    'isabstract' => false,
    'namespace' => 'Predis\\Pipeline',
    'extends' => 'MilliCache\\Deps\\Predis\\Pipeline\\ConnectionErrorProof',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Pipeline\\FireAndForget' => 
  array (
    'type' => 'class',
    'classname' => 'FireAndForget',
    'isabstract' => false,
    'namespace' => 'Predis\\Pipeline',
    'extends' => 'MilliCache\\Deps\\Predis\\Pipeline\\FireAndForget',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Pipeline\\Pipeline' => 
  array (
    'type' => 'class',
    'classname' => 'Pipeline',
    'isabstract' => false,
    'namespace' => 'Predis\\Pipeline',
    'extends' => 'MilliCache\\Deps\\Predis\\Pipeline\\Pipeline',
    'implements' => 
    array (
      0 => 'Predis\\ClientContextInterface',
    ),
  ),
  'Predis\\Pipeline\\RelayAtomic' => 
  array (
    'type' => 'class',
    'classname' => 'RelayAtomic',
    'isabstract' => false,
    'namespace' => 'Predis\\Pipeline',
    'extends' => 'MilliCache\\Deps\\Predis\\Pipeline\\RelayAtomic',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Pipeline\\RelayPipeline' => 
  array (
    'type' => 'class',
    'classname' => 'RelayPipeline',
    'isabstract' => false,
    'namespace' => 'Predis\\Pipeline',
    'extends' => 'MilliCache\\Deps\\Predis\\Pipeline\\RelayPipeline',
    'implements' => 
    array (
    ),
  ),
  'Predis\\PredisException' => 
  array (
    'type' => 'class',
    'classname' => 'PredisException',
    'isabstract' => true,
    'namespace' => 'Predis',
    'extends' => 'MilliCache\\Deps\\Predis\\PredisException',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Protocol\\ProtocolException' => 
  array (
    'type' => 'class',
    'classname' => 'ProtocolException',
    'isabstract' => false,
    'namespace' => 'Predis\\Protocol',
    'extends' => 'MilliCache\\Deps\\Predis\\Protocol\\ProtocolException',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Protocol\\Text\\CompositeProtocolProcessor' => 
  array (
    'type' => 'class',
    'classname' => 'CompositeProtocolProcessor',
    'isabstract' => false,
    'namespace' => 'Predis\\Protocol\\Text',
    'extends' => 'MilliCache\\Deps\\Predis\\Protocol\\Text\\CompositeProtocolProcessor',
    'implements' => 
    array (
      0 => 'Predis\\Protocol\\ProtocolProcessorInterface',
    ),
  ),
  'Predis\\Protocol\\Text\\Handler\\BulkResponse' => 
  array (
    'type' => 'class',
    'classname' => 'BulkResponse',
    'isabstract' => false,
    'namespace' => 'Predis\\Protocol\\Text\\Handler',
    'extends' => 'MilliCache\\Deps\\Predis\\Protocol\\Text\\Handler\\BulkResponse',
    'implements' => 
    array (
      0 => 'Predis\\Protocol\\Text\\Handler\\ResponseHandlerInterface',
    ),
  ),
  'Predis\\Protocol\\Text\\Handler\\ErrorResponse' => 
  array (
    'type' => 'class',
    'classname' => 'ErrorResponse',
    'isabstract' => false,
    'namespace' => 'Predis\\Protocol\\Text\\Handler',
    'extends' => 'MilliCache\\Deps\\Predis\\Protocol\\Text\\Handler\\ErrorResponse',
    'implements' => 
    array (
      0 => 'Predis\\Protocol\\Text\\Handler\\ResponseHandlerInterface',
    ),
  ),
  'Predis\\Protocol\\Text\\Handler\\IntegerResponse' => 
  array (
    'type' => 'class',
    'classname' => 'IntegerResponse',
    'isabstract' => false,
    'namespace' => 'Predis\\Protocol\\Text\\Handler',
    'extends' => 'MilliCache\\Deps\\Predis\\Protocol\\Text\\Handler\\IntegerResponse',
    'implements' => 
    array (
      0 => 'Predis\\Protocol\\Text\\Handler\\ResponseHandlerInterface',
    ),
  ),
  'Predis\\Protocol\\Text\\Handler\\MultiBulkResponse' => 
  array (
    'type' => 'class',
    'classname' => 'MultiBulkResponse',
    'isabstract' => false,
    'namespace' => 'Predis\\Protocol\\Text\\Handler',
    'extends' => 'MilliCache\\Deps\\Predis\\Protocol\\Text\\Handler\\MultiBulkResponse',
    'implements' => 
    array (
      0 => 'Predis\\Protocol\\Text\\Handler\\ResponseHandlerInterface',
    ),
  ),
  'Predis\\Protocol\\Text\\Handler\\StatusResponse' => 
  array (
    'type' => 'class',
    'classname' => 'StatusResponse',
    'isabstract' => false,
    'namespace' => 'Predis\\Protocol\\Text\\Handler',
    'extends' => 'MilliCache\\Deps\\Predis\\Protocol\\Text\\Handler\\StatusResponse',
    'implements' => 
    array (
      0 => 'Predis\\Protocol\\Text\\Handler\\ResponseHandlerInterface',
    ),
  ),
  'Predis\\Protocol\\Text\\Handler\\StreamableMultiBulkResponse' => 
  array (
    'type' => 'class',
    'classname' => 'StreamableMultiBulkResponse',
    'isabstract' => false,
    'namespace' => 'Predis\\Protocol\\Text\\Handler',
    'extends' => 'MilliCache\\Deps\\Predis\\Protocol\\Text\\Handler\\StreamableMultiBulkResponse',
    'implements' => 
    array (
      0 => 'Predis\\Protocol\\Text\\Handler\\ResponseHandlerInterface',
    ),
  ),
  'Predis\\Protocol\\Text\\ProtocolProcessor' => 
  array (
    'type' => 'class',
    'classname' => 'ProtocolProcessor',
    'isabstract' => false,
    'namespace' => 'Predis\\Protocol\\Text',
    'extends' => 'MilliCache\\Deps\\Predis\\Protocol\\Text\\ProtocolProcessor',
    'implements' => 
    array (
      0 => 'Predis\\Protocol\\ProtocolProcessorInterface',
    ),
  ),
  'Predis\\Protocol\\Text\\RequestSerializer' => 
  array (
    'type' => 'class',
    'classname' => 'RequestSerializer',
    'isabstract' => false,
    'namespace' => 'Predis\\Protocol\\Text',
    'extends' => 'MilliCache\\Deps\\Predis\\Protocol\\Text\\RequestSerializer',
    'implements' => 
    array (
      0 => 'Predis\\Protocol\\RequestSerializerInterface',
    ),
  ),
  'Predis\\Protocol\\Text\\ResponseReader' => 
  array (
    'type' => 'class',
    'classname' => 'ResponseReader',
    'isabstract' => false,
    'namespace' => 'Predis\\Protocol\\Text',
    'extends' => 'MilliCache\\Deps\\Predis\\Protocol\\Text\\ResponseReader',
    'implements' => 
    array (
      0 => 'Predis\\Protocol\\ResponseReaderInterface',
    ),
  ),
  'Predis\\PubSub\\AbstractConsumer' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractConsumer',
    'isabstract' => true,
    'namespace' => 'Predis\\PubSub',
    'extends' => 'MilliCache\\Deps\\Predis\\PubSub\\AbstractConsumer',
    'implements' => 
    array (
      0 => 'Iterator',
    ),
  ),
  'Predis\\PubSub\\Consumer' => 
  array (
    'type' => 'class',
    'classname' => 'Consumer',
    'isabstract' => false,
    'namespace' => 'Predis\\PubSub',
    'extends' => 'MilliCache\\Deps\\Predis\\PubSub\\Consumer',
    'implements' => 
    array (
    ),
  ),
  'Predis\\PubSub\\DispatcherLoop' => 
  array (
    'type' => 'class',
    'classname' => 'DispatcherLoop',
    'isabstract' => false,
    'namespace' => 'Predis\\PubSub',
    'extends' => 'MilliCache\\Deps\\Predis\\PubSub\\DispatcherLoop',
    'implements' => 
    array (
    ),
  ),
  'Predis\\PubSub\\RelayConsumer' => 
  array (
    'type' => 'class',
    'classname' => 'RelayConsumer',
    'isabstract' => false,
    'namespace' => 'Predis\\PubSub',
    'extends' => 'MilliCache\\Deps\\Predis\\PubSub\\RelayConsumer',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Replication\\MissingMasterException' => 
  array (
    'type' => 'class',
    'classname' => 'MissingMasterException',
    'isabstract' => false,
    'namespace' => 'Predis\\Replication',
    'extends' => 'MilliCache\\Deps\\Predis\\Replication\\MissingMasterException',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Replication\\ReplicationStrategy' => 
  array (
    'type' => 'class',
    'classname' => 'ReplicationStrategy',
    'isabstract' => false,
    'namespace' => 'Predis\\Replication',
    'extends' => 'MilliCache\\Deps\\Predis\\Replication\\ReplicationStrategy',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Replication\\RoleException' => 
  array (
    'type' => 'class',
    'classname' => 'RoleException',
    'isabstract' => false,
    'namespace' => 'Predis\\Replication',
    'extends' => 'MilliCache\\Deps\\Predis\\Replication\\RoleException',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Response\\Error' => 
  array (
    'type' => 'class',
    'classname' => 'Error',
    'isabstract' => false,
    'namespace' => 'Predis\\Response',
    'extends' => 'MilliCache\\Deps\\Predis\\Response\\Error',
    'implements' => 
    array (
      0 => 'Predis\\Response\\ErrorInterface',
    ),
  ),
  'Predis\\Response\\Iterator\\MultiBulk' => 
  array (
    'type' => 'class',
    'classname' => 'MultiBulk',
    'isabstract' => false,
    'namespace' => 'Predis\\Response\\Iterator',
    'extends' => 'MilliCache\\Deps\\Predis\\Response\\Iterator\\MultiBulk',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Response\\Iterator\\MultiBulkIterator' => 
  array (
    'type' => 'class',
    'classname' => 'MultiBulkIterator',
    'isabstract' => true,
    'namespace' => 'Predis\\Response\\Iterator',
    'extends' => 'MilliCache\\Deps\\Predis\\Response\\Iterator\\MultiBulkIterator',
    'implements' => 
    array (
      0 => 'Iterator',
      1 => 'Countable',
      2 => 'Predis\\Response\\ResponseInterface',
    ),
  ),
  'Predis\\Response\\Iterator\\MultiBulkTuple' => 
  array (
    'type' => 'class',
    'classname' => 'MultiBulkTuple',
    'isabstract' => false,
    'namespace' => 'Predis\\Response\\Iterator',
    'extends' => 'MilliCache\\Deps\\Predis\\Response\\Iterator\\MultiBulkTuple',
    'implements' => 
    array (
      0 => 'OuterIterator',
    ),
  ),
  'Predis\\Response\\ServerException' => 
  array (
    'type' => 'class',
    'classname' => 'ServerException',
    'isabstract' => false,
    'namespace' => 'Predis\\Response',
    'extends' => 'MilliCache\\Deps\\Predis\\Response\\ServerException',
    'implements' => 
    array (
      0 => 'Predis\\Response\\ErrorInterface',
    ),
  ),
  'Predis\\Response\\Status' => 
  array (
    'type' => 'class',
    'classname' => 'Status',
    'isabstract' => false,
    'namespace' => 'Predis\\Response',
    'extends' => 'MilliCache\\Deps\\Predis\\Response\\Status',
    'implements' => 
    array (
      0 => 'Predis\\Response\\ResponseInterface',
    ),
  ),
  'Predis\\Session\\Handler' => 
  array (
    'type' => 'class',
    'classname' => 'Handler',
    'isabstract' => false,
    'namespace' => 'Predis\\Session',
    'extends' => 'MilliCache\\Deps\\Predis\\Session\\Handler',
    'implements' => 
    array (
      0 => 'SessionHandlerInterface',
    ),
  ),
  'Predis\\Transaction\\AbortedMultiExecException' => 
  array (
    'type' => 'class',
    'classname' => 'AbortedMultiExecException',
    'isabstract' => false,
    'namespace' => 'Predis\\Transaction',
    'extends' => 'MilliCache\\Deps\\Predis\\Transaction\\AbortedMultiExecException',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Transaction\\MultiExec' => 
  array (
    'type' => 'class',
    'classname' => 'MultiExec',
    'isabstract' => false,
    'namespace' => 'Predis\\Transaction',
    'extends' => 'MilliCache\\Deps\\Predis\\Transaction\\MultiExec',
    'implements' => 
    array (
      0 => 'Predis\\ClientContextInterface',
    ),
  ),
  'Predis\\Transaction\\MultiExecState' => 
  array (
    'type' => 'class',
    'classname' => 'MultiExecState',
    'isabstract' => false,
    'namespace' => 'Predis\\Transaction',
    'extends' => 'MilliCache\\Deps\\Predis\\Transaction\\MultiExecState',
    'implements' => 
    array (
    ),
  ),
  'Predis\\Command\\Traits\\Aggregate' => 
  array (
    'type' => 'trait',
    'traitname' => 'Aggregate',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Aggregate',
    ),
  ),
  'Predis\\Command\\Traits\\BitByte' => 
  array (
    'type' => 'trait',
    'traitname' => 'BitByte',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\BitByte',
    ),
  ),
  'Predis\\Command\\Traits\\BloomFilters\\BucketSize' => 
  array (
    'type' => 'trait',
    'traitname' => 'BucketSize',
    'namespace' => 'Predis\\Command\\Traits\\BloomFilters',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\BloomFilters\\BucketSize',
    ),
  ),
  'Predis\\Command\\Traits\\BloomFilters\\Capacity' => 
  array (
    'type' => 'trait',
    'traitname' => 'Capacity',
    'namespace' => 'Predis\\Command\\Traits\\BloomFilters',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\BloomFilters\\Capacity',
    ),
  ),
  'Predis\\Command\\Traits\\BloomFilters\\Error' => 
  array (
    'type' => 'trait',
    'traitname' => 'Error',
    'namespace' => 'Predis\\Command\\Traits\\BloomFilters',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\BloomFilters\\Error',
    ),
  ),
  'Predis\\Command\\Traits\\BloomFilters\\Expansion' => 
  array (
    'type' => 'trait',
    'traitname' => 'Expansion',
    'namespace' => 'Predis\\Command\\Traits\\BloomFilters',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\BloomFilters\\Expansion',
    ),
  ),
  'Predis\\Command\\Traits\\BloomFilters\\Items' => 
  array (
    'type' => 'trait',
    'traitname' => 'Items',
    'namespace' => 'Predis\\Command\\Traits\\BloomFilters',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\BloomFilters\\Items',
    ),
  ),
  'Predis\\Command\\Traits\\BloomFilters\\MaxIterations' => 
  array (
    'type' => 'trait',
    'traitname' => 'MaxIterations',
    'namespace' => 'Predis\\Command\\Traits\\BloomFilters',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\BloomFilters\\MaxIterations',
    ),
  ),
  'Predis\\Command\\Traits\\BloomFilters\\NoCreate' => 
  array (
    'type' => 'trait',
    'traitname' => 'NoCreate',
    'namespace' => 'Predis\\Command\\Traits\\BloomFilters',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\BloomFilters\\NoCreate',
    ),
  ),
  'Predis\\Command\\Traits\\By\\ByArgument' => 
  array (
    'type' => 'trait',
    'traitname' => 'ByArgument',
    'namespace' => 'Predis\\Command\\Traits\\By',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\By\\ByArgument',
    ),
  ),
  'Predis\\Command\\Traits\\By\\ByLexByScore' => 
  array (
    'type' => 'trait',
    'traitname' => 'ByLexByScore',
    'namespace' => 'Predis\\Command\\Traits\\By',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\By\\ByLexByScore',
    ),
  ),
  'Predis\\Command\\Traits\\By\\GeoBy' => 
  array (
    'type' => 'trait',
    'traitname' => 'GeoBy',
    'namespace' => 'Predis\\Command\\Traits\\By',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\By\\GeoBy',
    ),
  ),
  'Predis\\Command\\Traits\\Count' => 
  array (
    'type' => 'trait',
    'traitname' => 'Count',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Count',
    ),
  ),
  'Predis\\Command\\Traits\\DB' => 
  array (
    'type' => 'trait',
    'traitname' => 'DB',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\DB',
    ),
  ),
  'Predis\\Command\\Traits\\Expire\\ExpireOptions' => 
  array (
    'type' => 'trait',
    'traitname' => 'ExpireOptions',
    'namespace' => 'Predis\\Command\\Traits\\Expire',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Expire\\ExpireOptions',
    ),
  ),
  'Predis\\Command\\Traits\\From\\GeoFrom' => 
  array (
    'type' => 'trait',
    'traitname' => 'GeoFrom',
    'namespace' => 'Predis\\Command\\Traits\\From',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\From\\GeoFrom',
    ),
  ),
  'Predis\\Command\\Traits\\Get\\Get' => 
  array (
    'type' => 'trait',
    'traitname' => 'Get',
    'namespace' => 'Predis\\Command\\Traits\\Get',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Get\\Get',
    ),
  ),
  'Predis\\Command\\Traits\\Json\\Indent' => 
  array (
    'type' => 'trait',
    'traitname' => 'Indent',
    'namespace' => 'Predis\\Command\\Traits\\Json',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Json\\Indent',
    ),
  ),
  'Predis\\Command\\Traits\\Json\\Newline' => 
  array (
    'type' => 'trait',
    'traitname' => 'Newline',
    'namespace' => 'Predis\\Command\\Traits\\Json',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Json\\Newline',
    ),
  ),
  'Predis\\Command\\Traits\\Json\\NxXxArgument' => 
  array (
    'type' => 'trait',
    'traitname' => 'NxXxArgument',
    'namespace' => 'Predis\\Command\\Traits\\Json',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Json\\NxXxArgument',
    ),
  ),
  'Predis\\Command\\Traits\\Json\\Space' => 
  array (
    'type' => 'trait',
    'traitname' => 'Space',
    'namespace' => 'Predis\\Command\\Traits\\Json',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Json\\Space',
    ),
  ),
  'Predis\\Command\\Traits\\Keys' => 
  array (
    'type' => 'trait',
    'traitname' => 'Keys',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Keys',
    ),
  ),
  'Predis\\Command\\Traits\\LeftRight' => 
  array (
    'type' => 'trait',
    'traitname' => 'LeftRight',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\LeftRight',
    ),
  ),
  'Predis\\Command\\Traits\\Limit\\Limit' => 
  array (
    'type' => 'trait',
    'traitname' => 'Limit',
    'namespace' => 'Predis\\Command\\Traits\\Limit',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Limit\\Limit',
    ),
  ),
  'Predis\\Command\\Traits\\Limit\\LimitObject' => 
  array (
    'type' => 'trait',
    'traitname' => 'LimitObject',
    'namespace' => 'Predis\\Command\\Traits\\Limit',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Limit\\LimitObject',
    ),
  ),
  'Predis\\Command\\Traits\\MinMaxModifier' => 
  array (
    'type' => 'trait',
    'traitname' => 'MinMaxModifier',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\MinMaxModifier',
    ),
  ),
  'Predis\\Command\\Traits\\Replace' => 
  array (
    'type' => 'trait',
    'traitname' => 'Replace',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Replace',
    ),
  ),
  'Predis\\Command\\Traits\\Rev' => 
  array (
    'type' => 'trait',
    'traitname' => 'Rev',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Rev',
    ),
  ),
  'Predis\\Command\\Traits\\Sorting' => 
  array (
    'type' => 'trait',
    'traitname' => 'Sorting',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Sorting',
    ),
  ),
  'Predis\\Command\\Traits\\Storedist' => 
  array (
    'type' => 'trait',
    'traitname' => 'Storedist',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Storedist',
    ),
  ),
  'Predis\\Command\\Traits\\Timeout' => 
  array (
    'type' => 'trait',
    'traitname' => 'Timeout',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Timeout',
    ),
  ),
  'Predis\\Command\\Traits\\To\\ServerTo' => 
  array (
    'type' => 'trait',
    'traitname' => 'ServerTo',
    'namespace' => 'Predis\\Command\\Traits\\To',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\To\\ServerTo',
    ),
  ),
  'Predis\\Command\\Traits\\Weights' => 
  array (
    'type' => 'trait',
    'traitname' => 'Weights',
    'namespace' => 'Predis\\Command\\Traits',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\Weights',
    ),
  ),
  'Predis\\Command\\Traits\\With\\WithCoord' => 
  array (
    'type' => 'trait',
    'traitname' => 'WithCoord',
    'namespace' => 'Predis\\Command\\Traits\\With',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\With\\WithCoord',
    ),
  ),
  'Predis\\Command\\Traits\\With\\WithDist' => 
  array (
    'type' => 'trait',
    'traitname' => 'WithDist',
    'namespace' => 'Predis\\Command\\Traits\\With',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\With\\WithDist',
    ),
  ),
  'Predis\\Command\\Traits\\With\\WithHash' => 
  array (
    'type' => 'trait',
    'traitname' => 'WithHash',
    'namespace' => 'Predis\\Command\\Traits\\With',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\With\\WithHash',
    ),
  ),
  'Predis\\Command\\Traits\\With\\WithScores' => 
  array (
    'type' => 'trait',
    'traitname' => 'WithScores',
    'namespace' => 'Predis\\Command\\Traits\\With',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\With\\WithScores',
    ),
  ),
  'Predis\\Command\\Traits\\With\\WithValues' => 
  array (
    'type' => 'trait',
    'traitname' => 'WithValues',
    'namespace' => 'Predis\\Command\\Traits\\With',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Traits\\With\\WithValues',
    ),
  ),
  'Predis\\Connection\\RelayMethods' => 
  array (
    'type' => 'trait',
    'traitname' => 'RelayMethods',
    'namespace' => 'Predis\\Connection',
    'use' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Connection\\RelayMethods',
    ),
  ),
  'MilliRules\\Actions\\ActionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ActionInterface',
    'namespace' => 'MilliRules\\Actions',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\MilliRules\\Actions\\ActionInterface',
    ),
  ),
  'MilliRules\\Conditions\\ConditionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ConditionInterface',
    'namespace' => 'MilliRules\\Conditions',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\MilliRules\\Conditions\\ConditionInterface',
    ),
  ),
  'MilliRules\\Packages\\PackageInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'PackageInterface',
    'namespace' => 'MilliRules\\Packages',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\MilliRules\\Packages\\PackageInterface',
    ),
  ),
  'Predis\\ClientContextInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ClientContextInterface',
    'namespace' => 'Predis',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\ClientContextInterface',
    ),
  ),
  'Predis\\ClientInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ClientInterface',
    'namespace' => 'Predis',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\ClientInterface',
    ),
  ),
  'Predis\\Cluster\\Distributor\\DistributorInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'DistributorInterface',
    'namespace' => 'Predis\\Cluster\\Distributor',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Cluster\\Distributor\\DistributorInterface',
    ),
  ),
  'Predis\\Cluster\\Hash\\HashGeneratorInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'HashGeneratorInterface',
    'namespace' => 'Predis\\Cluster\\Hash',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Cluster\\Hash\\HashGeneratorInterface',
    ),
  ),
  'Predis\\Cluster\\StrategyInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'StrategyInterface',
    'namespace' => 'Predis\\Cluster',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Cluster\\StrategyInterface',
    ),
  ),
  'Predis\\Command\\Argument\\ArrayableArgument' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ArrayableArgument',
    'namespace' => 'Predis\\Command\\Argument',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Argument\\ArrayableArgument',
    ),
  ),
  'Predis\\Command\\Argument\\Geospatial\\ByInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ByInterface',
    'namespace' => 'Predis\\Command\\Argument\\Geospatial',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Geospatial\\ByInterface',
    ),
  ),
  'Predis\\Command\\Argument\\Geospatial\\FromInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'FromInterface',
    'namespace' => 'Predis\\Command\\Argument\\Geospatial',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Geospatial\\FromInterface',
    ),
  ),
  'Predis\\Command\\Argument\\Search\\SchemaFields\\FieldInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'FieldInterface',
    'namespace' => 'Predis\\Command\\Argument\\Search\\SchemaFields',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Search\\SchemaFields\\FieldInterface',
    ),
  ),
  'Predis\\Command\\Argument\\Server\\LimitInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'LimitInterface',
    'namespace' => 'Predis\\Command\\Argument\\Server',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Argument\\Server\\LimitInterface',
    ),
  ),
  'Predis\\Command\\CommandInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'CommandInterface',
    'namespace' => 'Predis\\Command',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\CommandInterface',
    ),
  ),
  'Predis\\Command\\FactoryInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'FactoryInterface',
    'namespace' => 'Predis\\Command',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\FactoryInterface',
    ),
  ),
  'Predis\\Command\\PrefixableCommandInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'PrefixableCommandInterface',
    'namespace' => 'Predis\\Command',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\PrefixableCommandInterface',
    ),
  ),
  'Predis\\Command\\Processor\\ProcessorInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ProcessorInterface',
    'namespace' => 'Predis\\Command\\Processor',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Processor\\ProcessorInterface',
    ),
  ),
  'Predis\\Command\\Redis\\Container\\ContainerInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ContainerInterface',
    'namespace' => 'Predis\\Command\\Redis\\Container',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Redis\\Container\\ContainerInterface',
    ),
  ),
  'Predis\\Command\\Strategy\\StrategyResolverInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'StrategyResolverInterface',
    'namespace' => 'Predis\\Command\\Strategy',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Strategy\\StrategyResolverInterface',
    ),
  ),
  'Predis\\Command\\Strategy\\SubcommandStrategyInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'SubcommandStrategyInterface',
    'namespace' => 'Predis\\Command\\Strategy',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Command\\Strategy\\SubcommandStrategyInterface',
    ),
  ),
  'Predis\\Configuration\\OptionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'OptionInterface',
    'namespace' => 'Predis\\Configuration',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Configuration\\OptionInterface',
    ),
  ),
  'Predis\\Configuration\\OptionsInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'OptionsInterface',
    'namespace' => 'Predis\\Configuration',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Configuration\\OptionsInterface',
    ),
  ),
  'Predis\\Connection\\AggregateConnectionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'AggregateConnectionInterface',
    'namespace' => 'Predis\\Connection',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Connection\\AggregateConnectionInterface',
    ),
  ),
  'Predis\\Connection\\Cluster\\ClusterInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ClusterInterface',
    'namespace' => 'Predis\\Connection\\Cluster',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Connection\\Cluster\\ClusterInterface',
    ),
  ),
  'Predis\\Connection\\CompositeConnectionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'CompositeConnectionInterface',
    'namespace' => 'Predis\\Connection',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Connection\\CompositeConnectionInterface',
    ),
  ),
  'Predis\\Connection\\ConnectionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ConnectionInterface',
    'namespace' => 'Predis\\Connection',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Connection\\ConnectionInterface',
    ),
  ),
  'Predis\\Connection\\FactoryInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'FactoryInterface',
    'namespace' => 'Predis\\Connection',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Connection\\FactoryInterface',
    ),
  ),
  'Predis\\Connection\\NodeConnectionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'NodeConnectionInterface',
    'namespace' => 'Predis\\Connection',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Connection\\NodeConnectionInterface',
    ),
  ),
  'Predis\\Connection\\ParametersInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ParametersInterface',
    'namespace' => 'Predis\\Connection',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Connection\\ParametersInterface',
    ),
  ),
  'Predis\\Connection\\Replication\\ReplicationInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ReplicationInterface',
    'namespace' => 'Predis\\Connection\\Replication',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Connection\\Replication\\ReplicationInterface',
    ),
  ),
  'Predis\\Protocol\\ProtocolProcessorInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ProtocolProcessorInterface',
    'namespace' => 'Predis\\Protocol',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Protocol\\ProtocolProcessorInterface',
    ),
  ),
  'Predis\\Protocol\\RequestSerializerInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'RequestSerializerInterface',
    'namespace' => 'Predis\\Protocol',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Protocol\\RequestSerializerInterface',
    ),
  ),
  'Predis\\Protocol\\ResponseReaderInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ResponseReaderInterface',
    'namespace' => 'Predis\\Protocol',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Protocol\\ResponseReaderInterface',
    ),
  ),
  'Predis\\Protocol\\Text\\Handler\\ResponseHandlerInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ResponseHandlerInterface',
    'namespace' => 'Predis\\Protocol\\Text\\Handler',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Protocol\\Text\\Handler\\ResponseHandlerInterface',
    ),
  ),
  'Predis\\Response\\ErrorInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ErrorInterface',
    'namespace' => 'Predis\\Response',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Response\\ErrorInterface',
    ),
  ),
  'Predis\\Response\\ResponseInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ResponseInterface',
    'namespace' => 'Predis\\Response',
    'extends' => 
    array (
      0 => 'MilliCache\\Deps\\Predis\\Response\\ResponseInterface',
    ),
  ),
);

        public function __construct()
        {
            $this->includeFilePath = __DIR__ . '/autoload_alias.php';
        }

        public function autoload($class)
        {
            if (!isset($this->autoloadAliases[$class])) {
                return;
            }
            switch ($this->autoloadAliases[$class]['type']) {
                case 'class':
                        $this->load(
                            $this->classTemplate(
                                $this->autoloadAliases[$class]
                            )
                        );
                    break;
                case 'interface':
                    $this->load(
                        $this->interfaceTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                case 'trait':
                    $this->load(
                        $this->traitTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                default:
                    // Never.
                    break;
            }
        }

        private function load(string $includeFile)
        {
            file_put_contents($this->includeFilePath, $includeFile);
            include $this->includeFilePath;
            file_exists($this->includeFilePath) && unlink($this->includeFilePath);
        }

        private function classTemplate(array $class): string
        {
            $abstract = $class['isabstract'] ? 'abstract ' : '';
            $classname = $class['classname'];
            if (isset($class['namespace'])) {
                $namespace = "namespace {$class['namespace']};";
                $extends = '\\' . $class['extends'];
                $implements = empty($class['implements']) ? ''
                : ' implements \\' . implode(', \\', $class['implements']);
            } else {
                $namespace = '';
                $extends = $class['extends'];
                $implements = !empty($class['implements']) ? ''
                : ' implements ' . implode(', ', $class['implements']);
            }
            return <<<EOD
                <?php
                $namespace
                $abstract class $classname extends $extends $implements {}
                EOD;
        }

        private function interfaceTemplate(array $interface): string
        {
            $interfacename = $interface['interfacename'];
            $namespace = isset($interface['namespace'])
            ? "namespace {$interface['namespace']};" : '';
            $extends = isset($interface['namespace'])
            ? '\\' . implode('\\ ,', $interface['extends'])
            : implode(', ', $interface['extends']);
            return <<<EOD
                <?php
                $namespace
                interface $interfacename extends $extends {}
                EOD;
        }
        private function traitTemplate(array $trait): string
        {
            $traitname = $trait['traitname'];
            $namespace = isset($trait['namespace'])
            ? "namespace {$trait['namespace']};" : '';
            $uses = isset($trait['namespace'])
            ? '\\' . implode(';' . PHP_EOL . '    use \\', $trait['use'])
            : implode(';' . PHP_EOL . '    use ', $trait['use']);
            return <<<EOD
                <?php
                $namespace
                trait $traitname { 
                    use $uses; 
                }
                EOD;
        }
    }

    spl_autoload_register([ new AliasAutoloader(), 'autoload' ]);
}
