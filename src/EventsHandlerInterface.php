<?php
namespace noFlash\CherryHttp;

/**
 * Since CherryHttp code should NOT be edited events are introduced. Due to performance reasons only one EventsHandler
 * can be registered for single server instance.
 *
 * @package noFlash\CherryHttp
 */
interface EventsHandlerInterface
{
    /**
     * Called periodical in specified intervals
     * Note: you cannot raise HttpException from inside of heartbeat callback since there's no client known to handle
     * it.
     *
     * @return void
     * @see Server::setHearbeatInterval()
     */
    public function onHeartbeat();

    /**
     * Event is fired everytime client buffer gets empty after witting.
     * It was designed for applications requiring low memory consumption along with fast transfer rates while sending
     * large files over HTTP.
     * Simple demonstration of it's usage can be seen in "ContinuousStream" example.
     *
     * @param StreamServerNodeInterface $client
     *
     * @return void
     * @throws NodeDisconnectException
     * @throws ClientUpgradeException
     */
    public function onWriteBufferEmpty(StreamServerNodeInterface $client);


    /**
     * Event is fired everytime any HTTP exception occurs.
     * It can be used to generate pretty error messages (see PrettyErrors example) or perform additional logging
     * actions.
     *
     * @param HttpException $exception Raised exception containing proposed HttpResponse
     * @param StreamServerNodeInterface $client
     *
     * @return HttpResponse
     * @see HttpException::getResponse()
     */
    public function onHttpException(HttpException $exception, StreamServerNodeInterface $client);
}
