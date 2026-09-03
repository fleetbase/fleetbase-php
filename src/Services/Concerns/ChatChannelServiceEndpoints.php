<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait ChatChannelServiceEndpoints
{
    /**
     * Add Participant.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function addParticipant($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/chat-channels/:id/add-participant', ['id'], 'body', func_get_args());
    }

    /**
     * Create Chat Channel.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createChatChannel(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/chat-channels', [], 'body', func_get_args());
    }

    /**
     * Create Read Receipt.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function createReadReceipt($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/chat-channels/read-message/:chatMessageId', ['chatMessageId'], 'body', func_get_args());
    }

    /**
     * Delete Chat Channel.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function deleteChatChannel($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('DELETE', '{{base_url}}/{{namespace}}/chat-channels/:id', ['id'], 'body', func_get_args());
    }

    /**
     * Delete Message.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function deleteMessage($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('DELETE', '{{base_url}}/{{namespace}}/chat-channels/delete-message/:chatMessageId', ['chatMessageId'], 'body', func_get_args());
    }

    /**
     * List Available Participants.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function listAvailableParticipants(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/chat-channels/available-participants', [], 'query', func_get_args());
    }

    /**
     * Query Chat Channels.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryChatChannels(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/chat-channels', [], 'query', func_get_args());
    }

    /**
     * Remove Participant.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function removeParticipant($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('DELETE', '{{base_url}}/{{namespace}}/chat-channels/remove-participant/:participantId', ['participantId'], 'body', func_get_args());
    }

    /**
     * Retrieve Chat Channel.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function retrieveChatChannel($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/chat-channels/:id', ['id'], 'query', func_get_args());
    }

    /**
     * Send Message.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function sendMessage($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/chat-channels/:id/send-message', ['id'], 'body', func_get_args());
    }

    /**
     * Update Chat Channel.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function updateChatChannel($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PUT', '{{base_url}}/{{namespace}}/chat-channels/:id', ['id'], 'body', func_get_args());
    }
}
