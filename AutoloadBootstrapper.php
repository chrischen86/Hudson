<?php

require_once __DIR__ . '/dal' . '/DataAccessAdapter.php';
require_once __DIR__ . '/dal' . '/ModelBuildingHelper.php';
require_once __DIR__ . '/dal' . '/Phases.php';

require_once __DIR__ . '/dal/enums' . '/StateEnum.php';
require_once __DIR__ . '/dal/models' . '/CoreModel.php';
require_once __DIR__ . '/dal/models' . '/ConquestModel.php';
require_once __DIR__ . '/dal/models' . '/UserModel.php';
require_once __DIR__ . '/dal/models' . '/ZoneModel.php';
require_once __DIR__ . '/dal/models' . '/NodeModel.php';
require_once __DIR__ . '/dal/models' . '/StrikeModel.php';

require_once __DIR__ . '/dal/repositories' . '/CoreRepository.php';
require_once __DIR__ . '/dal/repositories' . '/ConquestRepository.php';
require_once __DIR__ . '/dal/repositories' . '/UserRepository.php';
require_once __DIR__ . '/dal/repositories' . '/ZoneRepository.php';
require_once __DIR__ . '/dal/repositories' . '/NodeRepository.php';
require_once __DIR__ . '/dal/repositories' . '/StrikeRepository.php';

require_once __DIR__ . '/framework/conquest' . '/ConquestManager.php';
require_once __DIR__ . '/framework/conquest' . '/StatsDto.php';

require_once __DIR__ . '/framework/donation' . '/GoogleClientInstance.php';
require_once __DIR__ . '/framework/donation' . '/DonationManager.php';
require_once __DIR__ . '/framework/donation' . '/DonationMessageDto.php';
require_once __DIR__ . '/framework/donation' . '/SheetManager.php';

require_once __DIR__ . '/framework' . '/ICommandProcessor.php';
require_once __DIR__ . '/framework' . '/CommandProcessorFactory.php';
require_once __DIR__ . '/framework' . '/InitCommandProcessor.php';
require_once __DIR__ . '/framework' . '/StrikeCommandProcessor.php';
require_once __DIR__ . '/framework' . '/StatusCommandProcessor.php';
require_once __DIR__ . '/framework' . '/NodeCallCommandProcessor.php';
require_once __DIR__ . '/framework' . '/HoldCommandProcessor.php';
require_once __DIR__ . '/framework' . '/ZoneCommandProcessor.php';
require_once __DIR__ . '/framework' . '/ClearCommandProcessor.php';
require_once __DIR__ . '/framework' . '/StatsCommandProcessor.php';
require_once __DIR__ . '/framework' . '/SummaryCommandProcessor.php';

require_once __DIR__ . '/framework' . '/slack' . '/SlackApi.php';

require_once __DIR__ . '/Config.php';