<div class="container">
    <div class="row justify-content-center" style="margin-top: 100px;">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-chart-line fa-3x text-primary"></i>
                        <h3 class="mt-3">KPI Portal</h3>
                        <p class="text-muted">Sign in to your account</p>
                    </div>
                    
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo base_url('authenticate'); ?>">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Enter your email" required autofocus
                                       value="<?php echo set_value('email'); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter your password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg mt-4">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </button>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            Test Credentials:<br>
                            Manager: arjun.patel@example.com<br>
                            Employee: rahul.sharma@example.com<br>
                            Password: password123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
