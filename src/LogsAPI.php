<?php

namespace DropTF;

class LogsAPI {
	public function getLogsForPlayer(\SteamID $steamId) {
		$content = file_get_contents('http://logs.tf/json_search?player=' . $steamId->ConvertToUInt64());
		$data = json_decode($content, true);
		return $data['logs'];
	}

	public function getLogsForPlayerSince(\SteamID $steamId, int $since) {
		$allLogs = $this->getLogsForPlayer($steamId);
		$steamId3 = $steamId->RenderSteam3();
		return array_filter($allLogs, function (array $log) use ($since, $steamId3) {
			return $log['date'] > $since;
		});
	}

	public function getLog(int $id) {
		$cached = apcu_fetch('log_' . $id);
		$content = $cached ?: file_get_contents('http://logs.tf/json/' . $id);
		if (!$cached) {
			apcu_store('log_' . $id, $content);
		}
		return json_decode($content, true);
	}

	public function getDropsFromLog(int $id, \SteamID $steamId) {
		$log = $this->getLog($id);
		$entry = $log['players'][$steamId->RenderSteam3()];
		return isset($entry['medicstats']) ? $entry['drops'] : null;
	}

	public function getDropsForPlayersSince(\SteamID $steamId, int $since) {
		$logs = $this->getLogsForPlayerSince($steamId, $since);
		$dropsPerLog = array_map(function (array $log) use ($steamId) {
			return $this->getDropsFromLog($log['id'], $steamId);
		}, $logs);
		$dropsPerLog = array_filter($dropsPerLog, function ($drops) {
			return !is_null($drops);
		});
		return [array_sum($dropsPerLog), count($dropsPerLog)];
	}
}
