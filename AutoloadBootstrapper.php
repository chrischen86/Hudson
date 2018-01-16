<?php

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/app' . '/bootstrap.php';

require_once __DIR__ . '/dal' . '/IDataAccessAdapter.php';
require_once __DIR__ . '/dal' . '/DataAccessAdapter.php';
require_once __DIR__ . '/dal' . '/NullDataAccessAdapter.php';
require_once __DIR__ . '/dal' . '/ModelBuildingHelper.php';
require_once __DIR__ . '/dal' . '/Phases.php';

require_once __DIR__ . '/dal/enums' . '/StateEnum.php';
require_once __DIR__ . '/dal/models' . '/CoreModel.php';
require_once __DIR__ . '/dal/models' . '/ConquestModel.php';
require_once __DIR__ . '/dal/models' . '/UserModel.php';
require_once __DIR__ . '/dal/models' . '/ZoneModel.php';
require_once __DIR__ . '/dal/models' . '/NodeModel.php';
require_once __DIR__ . '/dal/models' . '/StrikeModel.php';
require_once __DIR__ . '/dal/models' . '/ConsensusModel.php';

require_once __DIR__ . '/dal/repositories' . '/CoreRepository.php';
require_once __DIR__ . '/dal/repositories' . '/ConquestRepository.php';
require_once __DIR__ . '/dal/repositories' . '/UserRepository.php';
require_once __DIR__ . '/dal/repositories' . '/ZoneRepository.php';
require_once __DIR__ . '/dal/repositories' . '/NodeRepository.php';
require_once __DIR__ . '/dal/repositories' . '/StrikeRepository.php';
require_once __DIR__ . '/dal/repositories' . '/ConsensusRepository.php';

require_once __DIR__ . '/framework' . '/ReactionProcessor.php';

require_once __DIR__ . '/framework/conquest' . '/ConquestManager.php';
require_once __DIR__ . '/framework/conquest' . '/StatsDto.php';
require_once __DIR__ . '/framework/conquest' . '/SetupResultEnum.php';

require_once __DIR__ . '/framework/donation' . '/GoogleClientInstance.php';
require_once __DIR__ . '/framework/donation' . '/DonationManager.php';
require_once __DIR__ . '/framework/donation' . '/DonationMessageDto.php';
require_once __DIR__ . '/framework/donation' . '/SheetManager.php';

require_once __DIR__ . '/framework' . '/slack' . '/ISlackApi.php';
require_once __DIR__ . '/framework' . '/slack' . '/SlackApi.php';
require_once __DIR__ . '/framework' . '/slack' . '/NullSlackApi.php';

require_once __DIR__ . '/framework' . '/google' . '/ImageChartApi.php';

require_once __DIR__ . '/framework' . '/command' . '/ICommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/ClearCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/CommandStrategyFactory.php';
require_once __DIR__ . '/framework' . '/command' . '/InitCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/StrikeCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/StatusCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/NodeCallCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/HoldCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/ZoneCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/ClearCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/CancelCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/StatsCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/SummaryCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/LeadCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/TrainingModeCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/SummaryHistoryCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/ArchiveUserCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/UserListCommandStrategy.php';
require_once __DIR__ . '/framework' . '/system' . '/FileListCommandStrategy.php';
require_once __DIR__ . '/framework' . '/system' . '/DeleteFileCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/ConquestModeCommandStrategy.php';
require_once __DIR__ . '/framework' . '/command' . '/PersonalStatsCommandStrategy.php';

require_once __DIR__ . '/framework' . '/process' . '/ProcessManager.php';

require_once __DIR__ . '/framework' . '/system' . '/SlackFileManager.php';
require_once __DIR__ . '/framework' . '/system' . '/models' . '/FileInfoModel.php';
require_once __DIR__ . '/framework' . '/system' . '/models' . '/FileListModel.php';
require_once __DIR__ . '/framework' . '/system' . '/models' . '/PagingModel.php';