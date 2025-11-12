<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-star"></i> My Performance Reviews</h2>
        <p class="mb-0">View your performance review history</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Performance Reviews (<?php echo count($reviews); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No performance reviews available yet.
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="reviewsAccordion">
                            <?php foreach ($reviews as $index => $review): ?>
                            <div class="card mb-2">
                                <div class="card-header" id="heading<?php echo $index; ?>">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link btn-block text-left" type="button" 
                                                data-toggle="collapse" data-target="#collapse<?php echo $index; ?>">
                                            <i class="fas fa-calendar-alt"></i> 
                                            <strong><?php echo $review->period_name; ?></strong>
                                            (<?php echo date('M d, Y', strtotime($review->start_date)); ?> - 
                                            <?php echo date('M d, Y', strtotime($review->end_date)); ?>)
                                            
                                            <span class="float-right">
                                                Overall Score: <strong class="text-primary"><?php echo number_format($review->overall_score, 2); ?></strong>
                                                | Rating: 
                                                <span class="badge badge-<?php 
                                                    if ($review->overall_rating === 'EXCELLENT') echo 'success';
                                                    elseif ($review->overall_rating === 'GOOD') echo 'primary';
                                                    elseif ($review->overall_rating === 'AVERAGE') echo 'info';
                                                    else echo 'warning';
                                                ?>"><?php echo $review->overall_rating; ?></span>
                                            </span>
                                        </button>
                                    </h5>
                                </div>

                                <div id="collapse<?php echo $index; ?>" class="collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                                     data-parent="#reviewsAccordion">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h6 class="text-muted">Overall Score</h6>
                                                        <h2 class="text-primary"><?php echo number_format($review->overall_score, 2); ?></h2>
                                                        <small>out of 100</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h6 class="text-muted">Overall Rating</h6>
                                                        <h2>
                                                            <span class="badge badge-<?php 
                                                                if ($review->overall_rating === 'EXCELLENT') echo 'success';
                                                                elseif ($review->overall_rating === 'GOOD') echo 'primary';
                                                                elseif ($review->overall_rating === 'AVERAGE') echo 'info';
                                                                else echo 'warning';
                                                            ?>"><?php echo $review->overall_rating; ?></span>
                                                        </h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h6 class="text-muted">Reviewed By</h6>
                                                        <h5><?php echo $review->reviewer_first_name . ' ' . $review->reviewer_last_name; ?></h5>
                                                        <small><?php echo date('M d, Y', strtotime($review->review_date)); ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($review->comments): ?>
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-comments"></i> Manager's Comments:</h6>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($review->comments)); ?></p>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ($review->strengths): ?>
                                        <div class="alert alert-success">
                                            <h6><i class="fas fa-thumbs-up"></i> Strengths:</h6>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($review->strengths)); ?></p>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ($review->areas_for_improvement): ?>
                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-chart-line"></i> Areas for Improvement:</h6>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($review->areas_for_improvement)); ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
