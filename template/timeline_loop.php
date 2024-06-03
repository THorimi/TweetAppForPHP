<?php
                if($get_timeline):
                foreach($get_timeline as $tweet):
            ?>
                <li>
                    <?php if($tweet['repost_user_name']) : ?>
                        <p><?= $tweet['repost_user_name'] ?>さんがリポストしました</p>
                    <?php endif; ?>
                    <form action="user_page.php" method="POST">
                        <input type="hidden" name="user_id" value="<?= $tweet['auther_id'] ?>">
                        <input type="submit" name="user_page" value="<?= $tweet['auther_name'] ?>">
                    </form>
                    <p class="tweet_text">
                        <?= $tweet['tweet'] ?>
                    </p>
                    <p class="tweet_date">
                        <?= $tweet['original_post_time'] ?>
                    </p>
                    <div class="count_sec flex">
                        <p class=""><?= $tweet['repost_count'] ?>リポスト</p>
                        <p class=""><?= $tweet['favorite_count'] ?>いいね</p>
                    </div>
                    <div class="button_sec flex">
                        <form action="mypage.php" method="POST">
                            <input type="hidden" name="repost_id" value="<?= $tweet['tweet_id'] ?>">
                            <?php if($tweet['repost_flag'] === 0 || $tweet['repost_flag'] === '0') : ?>
                                <input type="submit" name="repost" value="リポストする">
                            <?php else: ?>
                                <input type="submit" name="repost" value="リポストを取り消す">
                            <?php endif; ?>
                        </form>
                        <form action="" method="POST">
                            <input type="hidden" name="favo_id" value="<?= $tweet['tweet_id'] ?>">
                            <?php if($tweet['favorite_flag'] === 0 || $tweet['favorite_flag'] === '0') : ?>
                                <input type="submit" name="favorite" value="いいねする">
                                <?php else: ?>
                                    <input type="submit" name="favorite" value="いいねを取り消す">
                            <?php endif; ?>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
            <?php else: ?>
                <li>つぶやきはまだありません</li>
            <?php endif;?>