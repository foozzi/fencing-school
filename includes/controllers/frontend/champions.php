<?

class Controller_Frontend_Champions extends Controller_Frontend
{
    /**
    * @see parent::getName()
    */
    public function getName()
    {
            return 'Чемпионы';
    }
    
    public function index()
    {
        return $this->getView()->render();
    }
    
    public function man()
    {
        return $this->getView()->render();
    }
    
    public function woman()
    {
        return $this->getView()->render();
    }
    
    public function man_one()
    {
        return $this->getView()->render();
    }
    
    public function woman_one()
    {
        return $this->getView()->render();
    }
}